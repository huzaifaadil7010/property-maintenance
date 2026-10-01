<?php

namespace App\Mcp\Resident;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ResidentIssueImageUpload
{
    private const int CHUNK_BYTES = 512 * 1024;

    private const int MAX_FILE_BYTES = 10 * 1024 * 1024;

    private const int LIFETIME_MINUTES = 30;

    private const array MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function upload(array $uploadInput, User $resident): array
    {
        $decodedChunkBytes = base64_decode($uploadInput['data'], true);
        $hasInvalidChunkBytes = $decodedChunkBytes === false
            || strlen($decodedChunkBytes) === 0
            || strlen($decodedChunkBytes) > self::CHUNK_BYTES;

        if ($hasInvalidChunkBytes) {
            throw ValidationException::withMessages(['data' => 'This image chunk is invalid or too large.']);
        }

        $uploadId = $uploadInput['upload_id'] ?? (string) Str::uuid();

        return Cache::lock('resident-mcp:upload-lock:'.$uploadId, 10)->block(3, function () use ($uploadInput, $resident, $decodedChunkBytes, $uploadId): array {
            $uploadCacheKey = self::cacheKey($uploadId);
            $uploadState = Cache::get($uploadCacheKey);
            $isFirstChunkOfNewUpload = $uploadInput['chunk_index'] === 0
                && $uploadState === null
                && ! isset($uploadInput['upload_id']);

            if ($isFirstChunkOfNewUpload) {
                self::prune();

                $stagedIssueImageCount = $resident->media()->where('collection_name', 'temp')->get()->filter(
                    fn (Media $stagedImageMedia): bool => $stagedImageMedia->getCustomProperty('resident_mcp_issue') === true
                )->count();
                $hasReachedStagedImageLimit = $stagedIssueImageCount >= 10;

                if ($hasReachedStagedImageLimit) {
                    throw ValidationException::withMessages(['images' => 'Finish or discard existing MCP issue images before uploading more.']);
                }

                $uploadState = [
                    'resident_id' => $resident->id,
                    'organization_id' => $resident->current_organization_id,
                    'name' => $uploadInput['name'],
                    'size' => $uploadInput['size'],
                    'sha256' => strtolower($uploadInput['sha256']),
                    'total_chunks' => $uploadInput['total_chunks'],
                    'next_index' => 0,
                ];
            }

            $hasMatchingUploadState = is_array($uploadState)
                && $uploadState['resident_id'] === $resident->id
                && $uploadState['organization_id'] === $resident->current_organization_id
                && $uploadState['name'] === $uploadInput['name']
                && $uploadState['size'] === $uploadInput['size']
                && $uploadState['sha256'] === strtolower($uploadInput['sha256'])
                && $uploadState['total_chunks'] === $uploadInput['total_chunks']
                && $uploadState['next_index'] === $uploadInput['chunk_index'];

            if (! $hasMatchingUploadState) {
                throw ValidationException::withMessages(['upload_id' => 'This image upload is invalid or expired. Start the upload again.']);
            }

            $hasInvalidFileSize = $uploadInput['size'] < 1 || $uploadInput['size'] > self::MAX_FILE_BYTES;
            $hasUnexpectedChunkCount = $uploadInput['total_chunks'] !== (int) ceil($uploadInput['size'] / self::CHUNK_BYTES);
            $hasUnexpectedChunkLength = strlen($decodedChunkBytes) !== min(
                self::CHUNK_BYTES,
                $uploadInput['size'] - ($uploadInput['chunk_index'] * self::CHUNK_BYTES),
            );
            $hasInvalidChunkLayout = $hasInvalidFileSize || $hasUnexpectedChunkCount || $hasUnexpectedChunkLength;

            if ($hasInvalidChunkLayout) {
                throw ValidationException::withMessages(['data' => 'The image upload size does not match its chunks.']);
            }

            $uploadDirectory = self::directory($uploadId);
            Storage::disk('local')->put($uploadDirectory.'/'.$uploadInput['chunk_index'], $decodedChunkBytes);
            $uploadState['next_index']++;
            Cache::put($uploadCacheKey, $uploadState, now()->addMinutes(self::LIFETIME_MINUTES));
            $hasMoreChunksToUpload = $uploadState['next_index'] < $uploadState['total_chunks'];

            if ($hasMoreChunksToUpload) {
                return ['upload_id' => $uploadId, 'next_index' => $uploadState['next_index']];
            }

            try {
                $assembledImagePath = Storage::disk('local')->path($uploadDirectory.'/complete');
                $assembledImageStream = fopen($assembledImagePath, 'wb');
                $cannotOpenAssembledImageStream = $assembledImageStream === false;

                if ($cannotOpenAssembledImageStream) {
                    throw ValidationException::withMessages(['images' => 'The image could not be assembled.']);
                }

                try {
                    for ($chunkIndex = 0; $chunkIndex < $uploadState['total_chunks']; $chunkIndex++) {
                        $storedChunkBytes = Storage::disk('local')->get($uploadDirectory.'/'.$chunkIndex);
                        fwrite($assembledImageStream, $storedChunkBytes);
                    }
                } finally {
                    fclose($assembledImageStream);
                }

                $detectedImageInfo = @getimagesize($assembledImagePath);
                $detectedMimeType = $detectedImageInfo['mime'] ?? null;
                $hasUnexpectedAssembledSize = filesize($assembledImagePath) !== $uploadState['size'];

                if ($hasUnexpectedAssembledSize) {
                    throw ValidationException::withMessages(['images' => 'The uploaded file must be a valid JPEG, PNG, or WebP image no larger than 10 MB.']);
                }

                $hasUnexpectedImageHash = hash_file('sha256', $assembledImagePath) !== $uploadState['sha256'];

                if ($hasUnexpectedImageHash) {
                    throw ValidationException::withMessages(['images' => 'The uploaded file must be a valid JPEG, PNG, or WebP image no larger than 10 MB.']);
                }

                $hasUnsupportedImageType = ! isset(self::MIME_EXTENSIONS[$detectedMimeType]);

                if ($hasUnsupportedImageType) {
                    throw ValidationException::withMessages(['images' => 'The uploaded file must be a valid JPEG, PNG, or WebP image no larger than 10 MB.']);
                }

                $hasExcessivePixelCount = $detectedImageInfo[0] * $detectedImageInfo[1] > 40_000_000;

                if ($hasExcessivePixelCount) {
                    throw ValidationException::withMessages(['images' => 'The uploaded file must be a valid JPEG, PNG, or WebP image no larger than 10 MB.']);
                }

                $stagedImageMedia = $resident->addMedia($assembledImagePath)
                    ->usingName($uploadState['name'])
                    ->usingFileName(Str::uuid().'.'.self::MIME_EXTENSIONS[$detectedMimeType])
                    ->withCustomProperties([
                        'resident_mcp_issue' => true,
                        'organization_id' => $uploadState['organization_id'],
                        'expires_at' => now()->addMinutes(self::LIFETIME_MINUTES)->timestamp,
                        'sha256' => $uploadState['sha256'],
                    ])
                    ->toMediaCollection('temp');

                return [
                    'image_id' => $stagedImageMedia->uuid,
                    'name' => $uploadState['name'],
                    'size' => $stagedImageMedia->size,
                    'mime_type' => $detectedMimeType,
                ];
            } finally {
                Storage::disk('local')->deleteDirectory($uploadDirectory);
                Cache::forget($uploadCacheKey);
            }
        });
    }

    public static function inspect(array $imageIds, User $resident): array
    {
        $stagedIssueImageDetails = [];

        foreach ($imageIds as $imageId) {
            $stagedImageMedia = $resident->media()->where('collection_name', 'temp')->where('uuid', $imageId)->first();
            $isMissingStagedImageMedia = $stagedImageMedia === null;

            if ($isMissingStagedImageMedia) {
                throw ValidationException::withMessages(['images' => 'One or more uploaded images expired or changed. Upload them again.']);
            }

            $isResidentMcpIssueImage = $stagedImageMedia->getCustomProperty('resident_mcp_issue') === true;
            $belongsToCurrentOrganization = $stagedImageMedia->getCustomProperty('organization_id') === $resident->current_organization_id;
            $isStagedImageWithinLifetime = $stagedImageMedia->getCustomProperty('expires_at', 0) > now()->timestamp;
            $hasInvalidStagedImageMetadata = ! $isResidentMcpIssueImage || ! $belongsToCurrentOrganization || ! $isStagedImageWithinLifetime;

            if ($hasInvalidStagedImageMetadata) {
                throw ValidationException::withMessages(['images' => 'One or more uploaded images expired or changed. Upload them again.']);
            }

            $stagedImagePath = $stagedImageMedia->getPath();
            $hasStagedImageFile = is_file($stagedImagePath);
            $matchesOriginalImageHash = $hasStagedImageFile
                && hash_file('sha256', $stagedImagePath) === $stagedImageMedia->getCustomProperty('sha256');
            $hasInvalidStagedImageFile = ! $hasStagedImageFile || ! $matchesOriginalImageHash;

            if ($hasInvalidStagedImageFile) {
                throw ValidationException::withMessages(['images' => 'One or more uploaded images expired or changed. Upload them again.']);
            }

            $stagedIssueImageDetails[] = [
                'image_id' => $imageId,
                'name' => $stagedImageMedia->name,
                'size' => $stagedImageMedia->size,
                'mime_type' => $stagedImageMedia->mime_type,
                'sha256' => $stagedImageMedia->getCustomProperty('sha256'),
            ];
        }

        return $stagedIssueImageDetails;
    }

    public static function discard(array $imageIds, User $resident): void
    {
        foreach ($imageIds as $imageId) {
            $stagedImageMedia = $resident->media()->where('collection_name', 'temp')->where('uuid', $imageId)->first();
            $isResidentMcpIssueImage = $stagedImageMedia?->getCustomProperty('resident_mcp_issue') === true;

            if ($isResidentMcpIssueImage) {
                $stagedImageMedia->delete();
            }
        }
    }

    public static function prune(): void
    {
        Media::query()
            ->where('model_type', (new User)->getMorphClass())
            ->where('collection_name', 'temp')
            ->where('created_at', '<', now()->subMinutes(self::LIFETIME_MINUTES))
            ->chunkById(100, function ($expiredMediaBatch): void {
                foreach ($expiredMediaBatch as $stagedImageMedia) {
                    $isResidentMcpIssueImage = $stagedImageMedia->getCustomProperty('resident_mcp_issue') === true;

                    if ($isResidentMcpIssueImage) {
                        $stagedImageMedia->delete();
                    }
                }
            });

        foreach (Storage::disk('local')->directories('mcp/resident-uploads') as $uploadDirectory) {
            $absoluteUploadDirectory = Storage::disk('local')->path($uploadDirectory);
            $isExpiredUploadDirectory = is_dir($absoluteUploadDirectory)
                && filemtime($absoluteUploadDirectory) < now()->subHour()->timestamp;

            if ($isExpiredUploadDirectory) {
                Storage::disk('local')->deleteDirectory($uploadDirectory);
            }
        }
    }

    private static function cacheKey(string $uploadId): string
    {
        return 'resident-mcp:image-upload:'.$uploadId;
    }

    private static function directory(string $uploadId): string
    {
        return 'mcp/resident-uploads/'.$uploadId;
    }
}
