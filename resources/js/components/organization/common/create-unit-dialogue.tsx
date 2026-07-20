import { useForm, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
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
    DialogTrigger,
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
import { store } from '@/wayfinder/App/Http/Controllers/Organization/UnitsController';

export type PropertyOption = {
    id: number;
    name: string;
};

export type UnitStatusOption = {
    label: string;
    value: string;
};

type CreateUnitFormData = {
    property_id: number | '';
    name: string;
    floor: string;
    status: string;
};

type CreateUnitDialogueProps = {
    properties: PropertyOption[];
    unitStatuses: UnitStatusOption[];
};

export default function CreateUnitDialogue({
    properties,
    unitStatuses,
}: CreateUnitDialogueProps) {
    const [open, setOpen] = useState(false);
    const { currentOrganization } = usePage().props;
    const form = useForm<CreateUnitFormData>({
        property_id: '',
        name: '',
        floor: '',
        status: unitStatuses[0]?.value ?? '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!currentOrganization) {
            return;
        }

        form.submit(store(currentOrganization.uuid), {
            only: ['units'],
            preserveScroll: true,
            onSuccess: () => {
                form.resetAndClearErrors();
                setOpen(false);
            },
        });
    }

    function handleOpenChange(nextOpen: boolean) {
        setOpen(nextOpen);

        if (!nextOpen) {
            form.resetAndClearErrors();
        }
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button
                    className="w-full sm:w-auto"
                    disabled={properties.length === 0}
                >
                    <Plus />
                    Create unit
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create unit</DialogTitle>
                    <DialogDescription>
                        Add a unit to a property in your current organization.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={submit} className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="unit-property">Property</Label>
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
                                id="unit-property"
                                className="w-full"
                                aria-invalid={Boolean(form.errors.property_id)}
                                aria-describedby={
                                    form.errors.property_id
                                        ? 'unit-property-error'
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
                            id="unit-property-error"
                            message={form.errors.property_id}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="unit-name">Name</Label>
                        <Input
                            id="unit-name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            placeholder="A-101"
                            aria-invalid={Boolean(form.errors.name)}
                            aria-describedby={
                                form.errors.name ? 'unit-name-error' : undefined
                            }
                            required
                        />
                        <InputError
                            id="unit-name-error"
                            message={form.errors.name}
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="unit-floor">Floor</Label>
                            <Input
                                id="unit-floor"
                                value={form.data.floor}
                                onChange={(event) =>
                                    form.setData('floor', event.target.value)
                                }
                                placeholder="1"
                                aria-invalid={Boolean(form.errors.floor)}
                                aria-describedby={
                                    form.errors.floor
                                        ? 'unit-floor-error'
                                        : undefined
                                }
                            />
                            <InputError
                                id="unit-floor-error"
                                message={form.errors.floor}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="unit-status">Status</Label>
                            <Select
                                value={form.data.status}
                                onValueChange={(value) =>
                                    form.setData('status', value)
                                }
                                required
                            >
                                <SelectTrigger
                                    id="unit-status"
                                    className="w-full"
                                    aria-invalid={Boolean(form.errors.status)}
                                    aria-describedby={
                                        form.errors.status
                                            ? 'unit-status-error'
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
                                id="unit-status-error"
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
                            {form.processing ? 'Creating unit' : 'Create unit'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
