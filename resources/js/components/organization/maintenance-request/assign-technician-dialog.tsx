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
import { Skeleton } from '@/components/ui/skeleton';
import { Spinner } from '@/components/ui/spinner';

export type TechnicianOption = {
    id: number;
    name: string;
};

export type AssignTechnicianFormData = {
    assigned_technician_id: number | '';
    notes: string;
};

type AssignTechnicianDialogProps = {
    technicians: TechnicianOption[] | undefined;
    isReassigning: boolean;
    data: AssignTechnicianFormData;
    setData: InertiaFormProps<AssignTechnicianFormData>['setData'];
    errors: InertiaFormProps<AssignTechnicianFormData>['errors'];
    processing: boolean;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function AssignTechnicianDialog({
    technicians,
    isReassigning,
    data,
    setData,
    errors,
    processing,
    onSubmit,
    open,
    onOpenChange,
}: AssignTechnicianDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {isReassigning
                            ? 'Reassign technician'
                            : 'Assign technician'}
                    </DialogTitle>
                    <DialogDescription>
                        {isReassigning
                            ? 'Pick a different available technician for this request.'
                            : 'Pick an available technician to assign to this request.'}
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={onSubmit} className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="assign-technician">Technician</Label>
                        {technicians === undefined ? (
                            <Skeleton className="h-9 w-full" />
                        ) : (
                            <Select
                                value={
                                    data.assigned_technician_id === ''
                                        ? ''
                                        : String(data.assigned_technician_id)
                                }
                                onValueChange={(value) =>
                                    setData(
                                        'assigned_technician_id',
                                        Number(value),
                                    )
                                }
                                required
                            >
                                <SelectTrigger
                                    id="assign-technician"
                                    className="w-full"
                                    aria-invalid={Boolean(
                                        errors.assigned_technician_id,
                                    )}
                                    aria-describedby={
                                        errors.assigned_technician_id
                                            ? 'assign-technician-error'
                                            : undefined
                                    }
                                >
                                    <SelectValue placeholder="Select a technician" />
                                </SelectTrigger>
                                <SelectContent>
                                    {technicians.map((technician) => (
                                        <SelectItem
                                            key={technician.id}
                                            value={String(technician.id)}
                                        >
                                            {technician.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        )}
                        <InputError
                            id="assign-technician-error"
                            message={errors.assigned_technician_id}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="assign-technician-notes">
                            Notes (optional)
                        </Label>
                        <textarea
                            id="assign-technician-notes"
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
                            disabled={
                                processing || data.assigned_technician_id === ''
                            }
                        >
                            {processing && <Spinner />}
                            {processing
                                ? 'Saving'
                                : isReassigning
                                  ? 'Reassign technician'
                                  : 'Assign technician'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
