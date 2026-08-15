import { useForm, useHttp, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useRef, useState } from 'react';
import type { FormEvent } from 'react';
import { toast } from 'sonner';
import InputError from '@/components/input-error';
import TempFileUploadRequest, {
    type TempFileUploadResponse,
} from '@/components/resident/maintenance-request/temp-file-upload-request';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import ImageUploadInput from '@/components/ui/image-upload-input';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import MaintenanceCategory from '@/wayfinder/App/Enums/MaintenanceCategory';
import MaintenancePriority from '@/wayfinder/App/Enums/MaintenancePriority';
import DeleteTempFileController from '@/wayfinder/App/Http/Controllers/DeleteTempFileController';
import { store } from '@/wayfinder/App/Http/Controllers/Resident/MaintenanceRequestsController';

type EnumOption = {
    label: string;
    value: string;
};

type FormData = {
    title: string;
    category: (typeof MaintenanceCategory)[keyof typeof MaintenanceCategory];
    priority: (typeof MaintenancePriority)[keyof typeof MaintenancePriority];
    description: string;
};

type DeleteFormData = {
    file_name: string;
};

type DeleteResponse = {
    message: string;
};

type ImagePreview = {
    id: string;
    url: string;
    fileName: string;
    isProcessing: boolean;
    progress: number | null;
};

type UploadJob = {
    id: string;
    file: File;
};

type CleanupQueue = {
    fileNames: string[];
    index: number;
    onComplete: () => void;
};

type CreateMaintenanceRequestDialogueProps = {
    categories: EnumOption[];
    priorities: EnumOption[];
    only: ['myRequests'];
};

export default function CreateMaintenanceRequestDialogue({
    categories,
    priorities,
    only,
}: CreateMaintenanceRequestDialogueProps) {
    const { temp_path: tempPath } = usePage<{ temp_path: string }>().props;
    const [open, setOpen] = useState(false);
    const [previews, setPreviews] = useState<ImagePreview[]>([]);
    const [uploadJobs, setUploadJobs] = useState<UploadJob[]>([]);
    const [isCleaningUp, setIsCleaningUp] = useState(false);
    const form = useForm<FormData>({
        title: '',
        category: MaintenanceCategory.GENERAL,
        priority: MaintenancePriority.NORMAL,
        description: '',
    });
    const deleteHttp = useHttp<DeleteFormData, DeleteResponse>({
        file_name: '',
    });
    const cleanupQueue = useRef<CleanupQueue | null>(null);
    const isFileProcessing =
        uploadJobs.length > 0 || deleteHttp.processing || isCleaningUp;
    const cannotSubmitError = (
        form.errors as Record<string, string | undefined>
    ).cannot_submit;

    function updatePreview(id: string, update: Partial<ImagePreview>) {
        setPreviews((current) =>
            current.map((preview) =>
                preview.id === id ? { ...preview, ...update } : preview,
            ),
        );
    }

    function handleUploadSuccess(
        id: string,
        response: TempFileUploadResponse,
    ): void {
        updatePreview(id, {
            fileName: response.file_name,
            isProcessing: false,
            progress: null,
            url: `${tempPath}/${response.file_name}`,
        });
        toast.success(response.message);
    }

    function handleUploadError(id: string, message: string): void {
        setPreviews((current) =>
            current.filter((preview) => preview.id !== id),
        );
        toast.error(message);
    }

    function handleUploadProgress(id: string, progress: number | null): void {
        updatePreview(id, { progress });
    }

    function handleUploadFinish(id: string): void {
        setUploadJobs((current) =>
            current.filter((uploadJob) => uploadJob.id !== id),
        );
    }

    function handleFilesChange(files: File | File[]): void {
        const selectedFiles = Array.isArray(files) ? files : [files];
        const jobs = selectedFiles.map((file) => {
            const id = crypto.randomUUID();

            return {
                id,
                file,
            };
        });

        setPreviews((current) => [
            ...current,
            ...jobs.map(({ id, file }) => ({
                id,
                url: '',
                fileName: file.name,
                isProcessing: true,
                progress: null,
            })),
        ]);
        setUploadJobs((current) => [...current, ...jobs]);
    }

    function showDeleteError(message: string): void {
        toast.error(message);
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

        const fileName = queue.fileNames[queue.index];
        deleteHttp.setData({ file_name: fileName });
        deleteHttp.delete(DeleteTempFileController.url(), {
            onSuccess: () => {
                queue.index += 1;
                startNextCleanup();
            },
            onError: (errors) => {
                showDeleteError(
                    String(
                        errors.file_name ??
                            'Something went wrong while removing the file.',
                    ),
                );
                queue.index += 1;
                startNextCleanup();
            },
            onHttpException: () => {
                showDeleteError(
                    'Something went wrong while removing the file.',
                );
                queue.index += 1;
                startNextCleanup();

                return false;
            },
            onNetworkError: () => {
                showDeleteError('Unable to connect while removing the file.');
                queue.index += 1;
                startNextCleanup();

                return false;
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

    function removePreview(preview: ImagePreview): void {
        if (isFileProcessing || preview.isProcessing) {
            return;
        }

        deleteHttp.setData({ file_name: preview.fileName });
        deleteHttp.delete(DeleteTempFileController.url(), {
            onSuccess: (response) => {
                setPreviews((current) =>
                    current.filter((item) => item.id !== preview.id),
                );
                toast.success(response.message);
            },
            onError: (errors) => {
                showDeleteError(
                    String(
                        errors.file_name ??
                            'Something went wrong while removing the file.',
                    ),
                );
            },
            onHttpException: () => {
                showDeleteError(
                    'Something went wrong while removing the file.',
                );

                return false;
            },
            onNetworkError: () => {
                showDeleteError('Unable to connect while removing the file.');

                return false;
            },
        });
    }

    function submit(event: FormEvent<HTMLFormElement>): void {
        event.preventDefault();

        form.submit(store(), {
            only,
            preserveScroll: true,
            onSuccess: () => {
                cleanupTempFiles(() => {
                    form.resetAndClearErrors();
                    setOpen(false);
                });
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
            setOpen(true);

            return;
        }

        if (form.processing || isFileProcessing) {
            return;
        }

        cleanupTempFiles(() => {
            form.resetAndClearErrors();
            setOpen(false);
        });
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button className="w-full sm:w-auto">
                    <Plus />
                    Report an issue
                </Button>
            </DialogTrigger>

            <DialogContent>
                {uploadJobs.map((job) => (
                    <TempFileUploadRequest
                        key={job.id}
                        id={job.id}
                        file={job.file}
                        onSuccess={handleUploadSuccess}
                        onError={handleUploadError}
                        onProgress={handleUploadProgress}
                        onFinish={handleUploadFinish}
                    />
                ))}

                <DialogHeader>
                    <DialogTitle>Report an issue</DialogTitle>
                    <DialogDescription>
                        Tell us about a maintenance issue in your residence.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={submit} className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="maintenance-request-title">Title</Label>
                        <Input
                            id="maintenance-request-title"
                            value={form.data.title}
                            onChange={(event) =>
                                form.setData('title', event.target.value)
                            }
                            placeholder="Leaking kitchen faucet"
                            aria-invalid={Boolean(form.errors.title)}
                            required
                        />
                        <InputError message={form.errors.title} />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="maintenance-request-category">
                                Category
                            </Label>
                            <Select
                                value={form.data.category}
                                onValueChange={(value) =>
                                    form.setData(
                                        'category',
                                        value as FormData['category'],
                                    )
                                }
                            >
                                <SelectTrigger id="maintenance-request-category">
                                    <SelectValue placeholder="Select a category" />
                                </SelectTrigger>
                                <SelectContent>
                                    {categories.map((option) => (
                                        <SelectItem
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={form.errors.category} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="maintenance-request-priority">
                                Priority
                            </Label>
                            <Select
                                value={form.data.priority}
                                onValueChange={(value) =>
                                    form.setData(
                                        'priority',
                                        value as FormData['priority'],
                                    )
                                }
                            >
                                <SelectTrigger id="maintenance-request-priority">
                                    <SelectValue placeholder="Select a priority" />
                                </SelectTrigger>
                                <SelectContent>
                                    {priorities.map((option) => (
                                        <SelectItem
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={form.errors.priority} />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="maintenance-request-description">
                            Description
                        </Label>
                        <textarea
                            id="maintenance-request-description"
                            value={form.data.description}
                            onChange={(event) =>
                                form.setData('description', event.target.value)
                            }
                            placeholder="Describe what is happening..."
                            aria-invalid={Boolean(form.errors.description)}
                            className="min-h-28 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring"
                            required
                        />
                        <InputError message={form.errors.description} />
                        <InputError message={cannotSubmitError} />
                    </div>

                    <ImageUploadInput
                        label="Upload images"
                        placeholder="Upload issue images"
                        multiple
                        previews={previews}
                        onChange={handleFilesChange}
                        onRemove={(index) => {
                            const preview = previews[index];

                            if (preview) {
                                removePreview(preview);
                            }
                        }}
                        disabled={form.processing || isFileProcessing}
                        maxFiles={10}
                    />

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="secondary"
                                disabled={form.processing || isFileProcessing}
                            >
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            disabled={form.processing || isFileProcessing}
                        >
                            {(form.processing || isFileProcessing) && (
                                <Spinner />
                            )}
                            {form.processing || isFileProcessing
                                ? 'Working'
                                : 'Report issue'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
