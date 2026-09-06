<?php

namespace App\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasMediaLibraryUploadHelpers
{
    private const TEMP_COLLECTION = 'temp';

    /**
     * @return array{uuid: string, url: string, srcset: ?string}
     */
    public static function storeFileInTempMedia(UploadedFile $file, HasMedia $owner): array
    {
        $media = $owner->addMedia($file)
            ->withResponsiveImages()
            ->toMediaCollection(self::TEMP_COLLECTION)
            ->fresh();

        return [
            'uuid' => $media->uuid,
            'url' => $media->getUrl(),
            'srcset' => $media->hasResponsiveImages() ? $media->getSrcset() : null,
        ];
    }

    public static function deleteFileFromTempMedia(string $uuid, HasMedia $owner): void
    {
        static::findTempMedia($uuid, $owner)->delete();
    }

    public static function moveMediaFromTempToPermanent(
        string $uuid,
        HasMedia $tempOwner,
        HasMedia $permanentOwner,
        string $permanentCollection,
    ): Media {
        return static::findTempMedia($uuid, $tempOwner)->move($permanentOwner, $permanentCollection);
    }

    private static function findTempMedia(string $uuid, HasMedia $owner): Media
    {
        $media = $owner->getMedia(self::TEMP_COLLECTION)->firstWhere('uuid', $uuid);

        if (! $media) {
            throw ValidationException::withMessages([
                'file' => __('Something went wrong while processing the file. Please try again.'),
            ]);
        }

        return $media;
    }
}
