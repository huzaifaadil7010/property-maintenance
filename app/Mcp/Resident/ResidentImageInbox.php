<?php

namespace App\Mcp\Resident;

use Illuminate\Validation\ValidationException;

class ResidentImageInbox
{
    private const int MAX_FILE_BYTES = 10 * 1024 * 1024;

    private const array MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    public static function directory(): string
    {
        return storage_path('app/private/mcp/resident-inbox');
    }

    public static function inspect(array $names): array
    {
        if (! is_dir(self::directory()) || is_link(self::directory())) {
            throw ValidationException::withMessages(['images' => 'Create the local image inbox at '.self::directory().' before preparing this request.']);
        }

        $images = [];

        foreach ($names as $name) {
            if (! is_string($name) || $name === '' || basename($name) !== $name || str_contains($name, '/') || str_contains($name, '\\')
                || preg_match('/[\x00-\x1F]/', $name)) {
                throw ValidationException::withMessages(['images' => 'Image entries must be filenames in the resident inbox, not paths.']);
            }

            $path = self::directory().DIRECTORY_SEPARATOR.$name;
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if (! isset(self::MIME_TYPES[$extension]) || is_link($path) || ! is_file($path) || ! is_readable($path)) {
                throw ValidationException::withMessages(['images' => 'One or more images are missing, unreadable, linked, or have an unsupported extension.']);
            }

            $size = filesize($path);
            $info = @getimagesize($path);

            if ($size === false || $size === 0 || $size > self::MAX_FILE_BYTES || $info === false || $info['mime'] !== self::MIME_TYPES[$extension]
                || $info[0] * $info[1] > 40_000_000) {
                throw ValidationException::withMessages(['images' => 'Images must be valid JPEG, PNG, or WebP files no larger than 10 MB each.']);
            }

            $sha256 = hash_file('sha256', $path);

            if ($sha256 === false) {
                throw ValidationException::withMessages(['images' => 'An image could not be read completely.']);
            }

            $images[] = [
                'name' => $name,
                'size' => $size,
                'mime_type' => $info['mime'],
                'sha256' => $sha256,
            ];
        }

        return $images;
    }
}
