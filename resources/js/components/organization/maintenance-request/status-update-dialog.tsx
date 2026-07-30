import type { InertiaFormProps } from '@inertiajs/react';
import type { FormEvent } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';

export type MaintenanceRequestStatusOption = {
    label: string;
    value: string;
};

export type StatusUpdateFormData = {
    status: string;
    notes: string;
};

type StatusUpdateDialogProps = {
    statuses: MaintenanceRequestStatusOption[];
    data: StatusUpdateFormData;
    setData: InertiaFormProps<StatusUpdateFormData>['setData'];
    errors: InertiaFormProps<StatusUpdateFormData>['errors'];
    processing: boolean;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function StatusUpdateDialog({
    statuses,
    data,
    setData,
    errors,
    processing,
    onSubmit,
    open,
    onOpenChange,
}: StatusUpdateDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Update status</DialogTitle>
                    <DialogDescription>
                        Change the status of this maintenance request.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={onSubmit} className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="status-update-status">Status</Label>
                        <Select
                            value={data.status}
                            onValueChange={(value) =>
                                setData('status', value)
                            }
                            required
                        >
                            <SelectTrigger
                                id="status-update-status"
                                className="w-full"
                                aria-invalid={Boolean(errors.status)}
                                aria-describedby={
                                    errors.status
                                        ? 'status-update-status-error'
                                        : undefined
                                }
                            >
                                <SelectValue placeholder="Select a status" />
                            </SelectTrigger>
                            <SelectContent>
                                {statuses.map((status) => (
                                    <SelectItem
                                        key={status.value}
                                        value={status.value}
                                    >
                                        {status.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError
                            id="status-update-status-error"
                            message={errors.status}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="status-update-notes">
                            Notes (optional)
                        </Label>
                        <textarea
                            id="status-update-notes"
                            value={data.notes}
                            onChange={(event) =>
                                setData('notes', event.target.value)
                            }
                            rows={3}
                            className="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 flex w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                        />
                        <InputError message={errors.notes} />
                    </div>

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="secondary"
                                disabled={processing}
                            >
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            disabled={processing || data.status === ''}
                        >
                            {processing && <Spinner />}
                            {processing ? 'Updating status' : 'Update status'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
