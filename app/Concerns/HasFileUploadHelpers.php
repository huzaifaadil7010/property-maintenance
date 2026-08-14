<?php

namespace App\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait HasFileUploadHelpers
{
    public static function storeFileInTemp(UploadedFile $file, ?string $customDirectory = null): string
    {
        $directory = static::tempDirectory($customDirectory);
        $extension = $file->extension();
        $fileName = (string) Str::uuid().($extension ? ".{$extension}" : '');

        $storedPath = Storage::disk('public')->putFileAs($directory, $file, $fileName);

        if (! $storedPath) {
            throw ValidationException::withMessages([
                'file' => __('Something went wrong while storing the file.'),
            ]);
        }

        return $fileName;
    }

    public static function deleteFileFromTemp(string $fileName, ?string $customDirectory = null): void
    {
        if (blank($fileName) || basename($fileName) !== $fileName) {
            throw ValidationException::withMessages([
                'file' => __('Something went wrong while removing the file.'),
            ]);
        }

        $path = static::tempDirectory($customDirectory).'/'.$fileName;
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            throw ValidationException::withMessages([
                'file' => __('Something went wrong while removing the file.'),
            ]);
        }

        $disk->delete($path);
    }

    private static function tempDirectory(?string $customDirectory): string
    {
        $directory = str_replace('\\', '/', $customDirectory ?? '');

        if (
            str_starts_with($directory, '/')
            || preg_match('/^[A-Za-z]:\//', $directory) === 1
            || preg_match('#(?:^|/)\.\.(?:/|$)#', $directory) === 1
        ) {
            throw ValidationException::withMessages([
                'file' => __('Something went wrong while removing the file.'),
            ]);
        }

        $directory = trim($directory, '/');

        return blank($directory) ? 'temp' : 'temp/'.$directory;
    }

}
