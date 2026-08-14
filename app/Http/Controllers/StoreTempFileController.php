<?php

namespace App\Http\Controllers;

use App\Concerns\HasFileUploadHelpers;
use App\Http\Requests\StoreTempFileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class StoreTempFileController extends Controller
{
    use HasFileUploadHelpers;

    public function __invoke(StoreTempFileRequest $request): JsonResponse
    {
        try {
            $fileName = self::storeFileInTemp($request->file('file'));

            return response()->json([
                'file_name' => $fileName,
                'message' => __('File uploaded successfully.'),
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'file' => $exception->errors()['file'][0] ?? __('Something went wrong while storing the file.'),
            ], 422);
        } catch (Throwable) {
            return response()->json([
                'file' => __('Something went wrong while storing the file.'),
            ], 500);
        }
    }
}
