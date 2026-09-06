<?php

namespace App\Http\Controllers;

use App\Concerns\HasMediaLibraryUploadHelpers;
use App\Http\Requests\DeleteTempFileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeleteTempFileController extends Controller
{
    use HasMediaLibraryUploadHelpers;

    public function __invoke(DeleteTempFileRequest $request): JsonResponse
    {
        try {
            self::deleteFileFromTempMedia($request->string('file_name')->toString(), $request->user());

            return response()->json([
                'message' => __('File deleted successfully.'),
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'file_name' => $exception->errors()['file'][0] ?? __('Something went wrong while removing the file.'),
            ], 422);
        } catch (Throwable) {
            return response()->json([
                'file_name' => __('Something went wrong while removing the file.'),
            ], 500);
        }
    }
}
