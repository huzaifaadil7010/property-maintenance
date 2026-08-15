import { useHttp } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import StoreTempFileController from '@/wayfinder/App/Http/Controllers/StoreTempFileController';

type UploadFormData = {
    file: File;
    file_type: string;
};

export type TempFileUploadResponse = {
    file_name: string;
    message: string;
};

type TempFileUploadRequestProps = {
    id: string;
    file: File;
    onSuccess: (id: string, response: TempFileUploadResponse) => void;
    onError: (id: string, message: string) => void;
    onProgress: (id: string, progress: number | null) => void;
    onFinish: (id: string) => void;
};

export default function TempFileUploadRequest({
    id,
    file,
    onSuccess,
    onError,
    onProgress,
    onFinish,
}: TempFileUploadRequestProps) {
    const { post } = useHttp<UploadFormData, TempFileUploadResponse>({
        file,
        file_type: 'image',
    });
    const completed = useRef(false);
    const callbacks = useRef({
        onSuccess,
        onError,
        onProgress,
        onFinish,
    });

    callbacks.current = { onSuccess, onError, onProgress, onFinish };

    useEffect(() => {
        post(StoreTempFileController.url(), {
            onProgress: (progress) => {
                callbacks.current.onProgress(id, progress.percentage ?? null);
            },
            onSuccess: (response) => {
                if (completed.current) {
                    return;
                }

                completed.current = true;
                callbacks.current.onSuccess(id, response);
            },
            onError: (errors) => {
                if (completed.current) {
                    return;
                }

                completed.current = true;
                callbacks.current.onError(
                    id,
                    String(
                        errors.file ??
                            'Something went wrong while uploading the file.',
                    ),
                );
            },
            onHttpException: () => {
                if (completed.current) {
                    return false;
                }

                completed.current = true;
                callbacks.current.onError(
                    id,
                    'Something went wrong while uploading the file.',
                );

                return false;
            },
            onNetworkError: () => {
                if (completed.current) {
                    return false;
                }

                completed.current = true;
                callbacks.current.onError(
                    id,
                    'Unable to connect while uploading the file.',
                );

                return false;
            },
            onFinish: () => callbacks.current.onFinish(id),
        });
    }, [id, post]);

    return null;
}
