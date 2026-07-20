import { useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';

import InputError from '@/components/input-error';
import type {
    PropertyOption,
    UnitStatusOption,
} from '@/components/organization/common/create-unit-dialogue';
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
import { update } from '@/wayfinder/App/Http/Controllers/Organization/UnitsController';

type EditUnitFormData = {
    property_id: number | '';
    name: string;
    floor: string;
    status: string;
};

export type EditableUnit = {
    id: string | number;
    name: string;
    property: PropertyOption;
    floor: string | null;
    status: UnitStatusOption;
};

type EditUnitDialogueProps = {
    unit: EditableUnit;
    properties: PropertyOption[];
    unitStatuses: UnitStatusOption[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function EditUnitDialogue({
    unit,
    properties,
    unitStatuses,
    open,
    onOpenChange,
}: EditUnitDialogueProps) {
    const { currentOrganization } = usePage().props;
    const form = useForm<EditUnitFormData>({
        property_id: unit.property.id,
        name: unit.name,
        floor: unit.floor ?? '',
        status: unit.status.value,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!currentOrganization) {
            return;
        }

        form.submit(
            update({
                organization: currentOrganization.uuid,
                unit: unit.id,
            }),
            {
                only: ['units'],
                preserveScroll: true,
                onSuccess: () => {
                    form.resetAndClearErrors();
                    onOpenChange(false);
                },
            },
        );
    }

    function handleOpenChange(nextOpen: boolean) {
        onOpenChange(nextOpen);

        if (!nextOpen) {
            form.resetAndClearErrors();
        }
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit unit</DialogTitle>
                    <DialogDescription>
                        Update the unit details below.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={submit} className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="edit-unit-property">Property</Label>
                        <Select
                            value={
                                form.data.property_id === ''
                                    ? ''
                                    : String(form.data.property_id)
                            }
                            onValueChange={(value) =>
                                form.setData('property_id', Number(value))
                            }
                            required
                        >
                            <SelectTrigger
                                id="edit-unit-property"
                                className="w-full"
                                aria-invalid={Boolean(form.errors.property_id)}
                                aria-describedby={
                                    form.errors.property_id
                                        ? 'edit-unit-property-error'
                                        : undefined
                                }
                            >
                                <SelectValue placeholder="Select a property" />
                            </SelectTrigger>
                            <SelectContent>
                                {properties.map((property) => (
                                    <SelectItem
                                        key={property.id}
                                        value={String(property.id)}
                                    >
                                        {property.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError
                            id="edit-unit-property-error"
                            message={form.errors.property_id}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="edit-unit-name">Name</Label>
                        <Input
                            id="edit-unit-name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            aria-invalid={Boolean(form.errors.name)}
                            aria-describedby={
                                form.errors.name
                                    ? 'edit-unit-name-error'
                                    : undefined
                            }
                            required
                        />
                        <InputError
                            id="edit-unit-name-error"
                            message={form.errors.name}
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="edit-unit-floor">Floor</Label>
                            <Input
                                id="edit-unit-floor"
                                value={form.data.floor}
                                onChange={(event) =>
                                    form.setData('floor', event.target.value)
                                }
                                aria-invalid={Boolean(form.errors.floor)}
                                aria-describedby={
                                    form.errors.floor
                                        ? 'edit-unit-floor-error'
                                        : undefined
                                }
                            />
                            <InputError
                                id="edit-unit-floor-error"
                                message={form.errors.floor}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="edit-unit-status">Status</Label>
                            <Select
                                value={form.data.status}
                                onValueChange={(value) =>
                                    form.setData('status', value)
                                }
                                required
                            >
                                <SelectTrigger
                                    id="edit-unit-status"
                                    className="w-full"
                                    aria-invalid={Boolean(form.errors.status)}
                                    aria-describedby={
                                        form.errors.status
                                            ? 'edit-unit-status-error'
                                            : undefined
                                    }
                                >
                                    <SelectValue placeholder="Select a status" />
                                </SelectTrigger>
                                <SelectContent>
                                    {unitStatuses.map((status) => (
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
                                id="edit-unit-status-error"
                                message={form.errors.status}
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="secondary"
                                disabled={form.processing}
                            >
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            disabled={
                                form.processing ||
                                currentOrganization === null ||
                                form.data.property_id === '' ||
                                form.data.status === ''
                            }
                        >
                            {form.processing && <Spinner />}
                            {form.processing ? 'Updating unit' : 'Update unit'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
