<?php

namespace App\Http\Controllers;

use App\Concerns\HasMediaLibraryUploadHelpers;
use App\Http\Requests\StoreTempFileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class StoreTempFileController extends Controller
{
    use HasMediaLibraryUploadHelpers;

    public function __invoke(StoreTempFileRequest $request): JsonResponse
    {
        try {
            $result = self::storeFileInTempMedia($request->file('file'), $request->user());

            return response()->json([
                'file_name' => $result['uuid'],
                'url' => $result['url'],
                'srcset' => $result['srcset'],
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
