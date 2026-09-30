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

    public static function upload(array $input, User $resident): array
    {
        $bytes = base64_decode($input['data'], true);

        if ($bytes === false || strlen($bytes) === 0 || strlen($bytes) > self::CHUNK_BYTES) {
            throw ValidationException::withMessages(['data' => 'This image chunk is invalid or too large.']);
        }

        $uploadId = $input['upload_id'] ?? (string) Str::uuid();

        return Cache::lock('resident-mcp:upload-lock:'.$uploadId, 10)->block(3, function () use ($input, $resident, $bytes, $uploadId): array {
            $key = self::cacheKey($uploadId);
            $state = Cache::get($key);

            if ($input['chunk_index'] === 0 && $state === null && ! isset($input['upload_id'])) {
                self::prune();

                if ($resident->media()->where('collection_name', 'temp')->get()->filter(
                    fn (Media $media): bool => $media->getCustomProperty('resident_mcp_issue') === true
                )->count() >= 10) {
                    throw ValidationException::withMessages(['images' => 'Finish or discard existing MCP issue images before uploading more.']);
                }

                $state = [
                    'resident_id' => $resident->id,
                    'organization_id' => $resident->current_organization_id,
                    'name' => $input['name'],
                    'size' => $input['size'],
                    'sha256' => strtolower($input['sha256']),
                    'total_chunks' => $input['total_chunks'],
                    'next_index' => 0,
                ];
            }

            if (! is_array($state)
                || $state['resident_id'] !== $resident->id
                || $state['organization_id'] !== $resident->current_organization_id
                || $state['name'] !== $input['name']
                || $state['size'] !== $input['size']
                || $state['sha256'] !== strtolower($input['sha256'])
                || $state['total_chunks'] !== $input['total_chunks']
                || $state['next_index'] !== $input['chunk_index']) {
                throw ValidationException::withMessages(['upload_id' => 'This image upload is invalid or expired. Start the upload again.']);
            }

            if ($input['size'] < 1 || $input['size'] > self::MAX_FILE_BYTES
                || $input['total_chunks'] !== (int) ceil($input['size'] / self::CHUNK_BYTES)
                || strlen($bytes) !== min(self::CHUNK_BYTES, $input['size'] - ($input['chunk_index'] * self::CHUNK_BYTES))) {
                throw ValidationException::withMessages(['data' => 'The image upload size does not match its chunks.']);
            }

            $directory = self::directory($uploadId);
            Storage::disk('local')->put($directory.'/'.$input['chunk_index'], $bytes);
            $state['next_index']++;
            Cache::put($key, $state, now()->addMinutes(self::LIFETIME_MINUTES));

            if ($state['next_index'] < $state['total_chunks']) {
                return ['upload_id' => $uploadId, 'next_index' => $state['next_index']];
            }

            try {
                $path = Storage::disk('local')->path($directory.'/complete');
                $output = fopen($path, 'wb');

                if ($output === false) {
                    throw ValidationException::withMessages(['images' => 'The image could not be assembled.']);
                }

                try {
                    for ($index = 0; $index < $state['total_chunks']; $index++) {
                        $part = Storage::disk('local')->get($directory.'/'.$index);
                        fwrite($output, $part);
                    }
                } finally {
                    fclose($output);
                }

                $info = @getimagesize($path);
                $mime = $info['mime'] ?? null;

                if (filesize($path) !== $state['size']
                    || hash_file('sha256', $path) !== $state['sha256']
                    || ! isset(self::MIME_EXTENSIONS[$mime])
                    || $info[0] * $info[1] > 40_000_000) {
                    throw ValidationException::withMessages(['images' => 'The uploaded file must be a valid JPEG, PNG, or WebP image no larger than 10 MB.']);
                }

                $media = $resident->addMedia($path)
                    ->usingName($state['name'])
                    ->usingFileName(Str::uuid().'.'.self::MIME_EXTENSIONS[$mime])
                    ->withCustomProperties([
                        'resident_mcp_issue' => true,
                        'organization_id' => $state['organization_id'],
                        'expires_at' => now()->addMinutes(self::LIFETIME_MINUTES)->timestamp,
                        'sha256' => $state['sha256'],
                    ])
                    ->toMediaCollection('temp');

                return [
                    'image_id' => $media->uuid,
                    'name' => $state['name'],
                    'size' => $media->size,
                    'mime_type' => $mime,
                ];
            } finally {
                Storage::disk('local')->deleteDirectory($directory);
                Cache::forget($key);
            }
        });
    }

    public static function inspect(array $ids, User $resident): array
    {
        $images = [];

        foreach ($ids as $id) {
            $media = $resident->media()->where('collection_name', 'temp')->where('uuid', $id)->first();

            if ($media === null
                || $media->getCustomProperty('resident_mcp_issue') !== true
                || $media->getCustomProperty('organization_id') !== $resident->current_organization_id
                || $media->getCustomProperty('expires_at', 0) <= now()->timestamp
                || ! is_file($media->getPath())
                || hash_file('sha256', $media->getPath()) !== $media->getCustomProperty('sha256')) {
                throw ValidationException::withMessages(['images' => 'One or more uploaded images expired or changed. Upload them again.']);
            }

            $images[] = [
                'image_id' => $id,
                'name' => $media->name,
                'size' => $media->size,
                'mime_type' => $media->mime_type,
                'sha256' => $media->getCustomProperty('sha256'),
            ];
        }

        return $images;
    }

    public static function discard(array $ids, User $resident): void
    {
        foreach ($ids as $id) {
            $media = $resident->media()->where('collection_name', 'temp')->where('uuid', $id)->first();

            if ($media?->getCustomProperty('resident_mcp_issue') === true) {
                $media->delete();
            }
        }
    }

    public static function prune(): void
    {
        Media::query()
            ->where('model_type', (new User)->getMorphClass())
            ->where('collection_name', 'temp')
            ->where('created_at', '<', now()->subMinutes(self::LIFETIME_MINUTES))
            ->chunkById(100, function ($media): void {
                foreach ($media as $item) {
                    if ($item->getCustomProperty('resident_mcp_issue') === true) {
                        $item->delete();
                    }
                }
            });

        foreach (Storage::disk('local')->directories('mcp/resident-uploads') as $directory) {
            $path = Storage::disk('local')->path($directory);

            if (is_dir($path) && filemtime($path) < now()->subHour()->timestamp) {
                Storage::disk('local')->deleteDirectory($directory);
            }
        }
    }

    private static function cacheKey(string $id): string
    {
        return 'resident-mcp:image-upload:'.$id;
    }

    private static function directory(string $id): string
    {
        return 'mcp/resident-uploads/'.$id;
    }
}
