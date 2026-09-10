import { useForm, useHttp } from '@inertiajs/react';
import { useRef, useState } from 'react';
import type { FormEvent } from 'react';
import { toast } from 'sonner';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import ImageUploadInput from '@/components/ui/image-upload-input';
import type { ImageUploadPreview } from '@/components/ui/image-upload-input';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import DeleteTempFileController from '@/wayfinder/App/Http/Controllers/DeleteTempFileController';
import StoreTempFileController from '@/wayfinder/App/Http/Controllers/StoreTempFileController';
import CompleteMaintenanceRequestWorkController from '@/wayfinder/App/Http/Controllers/Technician/CompleteMaintenanceRequestWorkController';

type CompleteMaintenanceRequestDialogueProps = {
    jobId: number;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

type CompleteWorkFormData = {
    completion_notes: string;
    actual_cost: string;
    images: string[];
};

type UploadFormData = {
    file: File | null;
    file_type: string;
};

type TempFileUploadResponse = {
    file_name: string;
    url: string;
    srcset: string | null;
};

type DeleteFormData = {
    file_name: string;
};

type CleanupQueue = {
    fileNames: string[];
    index: number;
    onComplete: () => void;
};

type CompletionImagePreview = ImageUploadPreview & {
    id: string;
};

export default function CompleteMaintenanceRequestDialogue({
    jobId,
    open,
    onOpenChange,
}: CompleteMaintenanceRequestDialogueProps) {
    const [previews, setPreviews] = useState<CompletionImagePreview[]>([]);
    const [pendingUploads, setPendingUploads] = useState(0);
    const [isCleaningUp, setIsCleaningUp] = useState(false);
    const cleanupQueue = useRef<CleanupQueue | null>(null);
    const form = useForm<CompleteWorkFormData>({
        completion_notes: '',
        actual_cost: '',
        images: [],
    });
    const uploadHttp = useHttp<UploadFormData, TempFileUploadResponse>({
        file: null,
        file_type: 'image',
    });
    const deleteHttp = useHttp<DeleteFormData, { message: string }>({
        file_name: '',
    });
    const isFileProcessing =
        pendingUploads > 0 || deleteHttp.processing || isCleaningUp;

    function updatePreview(
        id: string,
        update: Partial<ImageUploadPreview>,
    ): void {
        setPreviews((current) =>
            current.map((preview) =>
                preview.id === id ? { ...preview, ...update } : preview,
            ),
        );
    }

    function handleFilesChange(files: File | File[]): void {
        const selectedFiles = (Array.isArray(files) ? files : [files]).map(
            (file) => ({ id: crypto.randomUUID(), file }),
        );

        setPreviews((current) => [
            ...current,
            ...selectedFiles.map(({ id, file }) => ({
                id,
                url: '',
                fileName: file.name,
                isProcessing: true,
                progress: null,
            })),
        ]);
        setPendingUploads((current) => current + selectedFiles.length);

        selectedFiles.forEach(({ id, file }) => {
            uploadHttp.setData({ file, file_type: 'image' });
            uploadHttp.post(StoreTempFileController.url(), {
                onProgress: (progress) => {
                    updatePreview(id, {
                        progress: progress.percentage ?? null,
                    });
                },
                onSuccess: (response) => {
                    updatePreview(id, {
                        fileName: response.file_name,
                        url: response.url,
                        srcSet: response.srcset ?? undefined,
                        isProcessing: false,
                        progress: null,
                    });
                    form.clearErrors('images');
                },
                onError: (errors) => {
                    setPreviews((current) =>
                        current.filter((preview) => preview.id !== id),
                    );
                    toast.error(
                        String(
                            errors.file ??
                                'Something went wrong while uploading the file.',
                        ),
                    );
                },
                onHttpException: () => {
                    toast.error(
                        'Something went wrong while uploading the file.',
                    );

                    return false;
                },
                onNetworkError: () => {
                    toast.error('Unable to connect while uploading the file.');

                    return false;
                },
                onFinish: () =>
                    setPendingUploads((current) => Math.max(0, current - 1)),
            });
        });
    }

    function startNextCleanup(): void {
        const queue = cleanupQueue.current;

        if (!queue) {
            return;
        }

        if (queue.index >= queue.fileNames.length) {
            cleanupQueue.current = null;
            setIsCleaningUp(false);
            setPreviews([]);
            queue.onComplete();

            return;
        }

        deleteHttp.setData({ file_name: queue.fileNames[queue.index] });
        deleteHttp.delete(DeleteTempFileController.url(), {
            onError: (errors) =>
                toast.error(
                    String(
                        errors.file_name ??
                            'Something went wrong while removing the file.',
                    ),
                ),
            onFinish: () => {
                queue.index += 1;
                startNextCleanup();
            },
        });
    }

    function cleanupTempFiles(onComplete: () => void): void {
        const fileNames = previews
            .filter((preview) => !preview.isProcessing)
            .map((preview) => preview.fileName);

        if (fileNames.length === 0) {
            setPreviews([]);
            onComplete();

            return;
        }

        setIsCleaningUp(true);
        cleanupQueue.current = { fileNames, index: 0, onComplete };
        startNextCleanup();
    }

    function removePreview(index: number): void {
        const preview = previews[index];

        if (!preview || preview.isProcessing || isFileProcessing) {
            return;
        }

        deleteHttp.setData({ file_name: preview.fileName });
        deleteHttp.delete(DeleteTempFileController.url(), {
            onSuccess: () =>
                setPreviews((current) =>
                    current.filter((_, previewIndex) => previewIndex !== index),
                ),
            onError: (errors) =>
                toast.error(
                    String(
                        errors.file_name ??
                            'Something went wrong while removing the file.',
                    ),
                ),
        });
    }

    function submit(event: FormEvent<HTMLFormElement>): void {
        event.preventDefault();

        if (previews.filter((preview) => !preview.isProcessing).length === 0) {
            form.setError(
                'images',
                'Upload at least one completion photo before completing work.',
            );

            return;
        }

        form.transform((data) => ({
            ...data,
            images: previews
                .filter((preview) => !preview.isProcessing)
                .map((preview) => preview.fileName),
        }));
        form.submit(CompleteMaintenanceRequestWorkController(jobId), {
            only: ['maintenanceRequest'],
            preserveScroll: true,
            onSuccess: () => {
                setPreviews([]);
                form.resetAndClearErrors();
                onOpenChange(false);
            },
            onError: (errors) => {
                if (errors.cannot_submit) {
                    toast.error(errors.cannot_submit);
                }
            },
        });
    }

    function handleOpenChange(nextOpen: boolean): void {
        if (nextOpen) {
            onOpenChange(true);

            return;
        }

        if (form.processing || isFileProcessing) {
            return;
        }

        cleanupTempFiles(() => {
            form.resetAndClearErrors();
            onOpenChange(false);
        });
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Complete work</DialogTitle>
                    <DialogDescription>
                        Add the completed work, actual cost, and supporting
                        photos.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={submit} className="grid gap-5">
                    <div className="grid gap-2">
                        <Label htmlFor="completion-notes">
                            Completion notes
                        </Label>
                        <textarea
                            id="completion-notes"
                            value={form.data.completion_notes}
                            onChange={(event) =>
                                form.setData(
                                    'completion_notes',
                                    event.target.value,
                                )
                            }
                            className="min-h-28 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            required
                        />
                        <InputError message={form.errors.completion_notes} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="actual-cost">Actual cost</Label>
                        <Input
                            id="actual-cost"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                            value={form.data.actual_cost}
                            onChange={(event) =>
                                form.setData('actual_cost', event.target.value)
                            }
                        />
                        <InputError message={form.errors.actual_cost} />
                    </div>
                    <ImageUploadInput
                        label="Completion photos"
                        description="PNG, JPG, WEBP - MAX 10MB"
                        placeholder="Upload completion photos"
                        multiple
                        previews={previews}
                        onChange={handleFilesChange}
                        onRemove={removePreview}
                        disabled={form.processing || isFileProcessing}
                    />
                    <InputError message={form.errors.images} />
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="secondary"
                            onClick={() => handleOpenChange(false)}
                            disabled={form.processing || isFileProcessing}
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            disabled={form.processing || isFileProcessing}
                        >
                            {form.processing && <Spinner />}
                            {form.processing ? 'Completing' : 'Complete work'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
