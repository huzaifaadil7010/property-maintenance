import { useForm, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
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
import { store } from '@/wayfinder/App/Http/Controllers/Organization/ResidentsController';

type ResidentCreatePropertyOption = {
    id: number;
    name: string;
    units_count?: number | null;
    available_units_count?: number | null;
};

type ResidentCreateUnitOption = {
    id: number;
    name: string;
    floor: string | null;
    property_id: number;
    property: {
        id: number;
        name: string;
    };
};

type ResidentCreateOptions = {
    properties: {
        data: ResidentCreatePropertyOption[];
    };
    availableUnits: {
        data: ResidentCreateUnitOption[];
    };
};

type CreateResidentFormData = {
    name: string;
    email: string;
    phone: string;
    property_id: number | '';
    unit_id: number | '';
};

type CreateResidentDialogueProps = {
    only: Array<'residents' | 'residentCreateOptions'>;
};

export default function CreateResidentDialogue({
    only,
}: CreateResidentDialogueProps) {
    const [open, setOpen] = useState(false);
    const { currentOrganization, residentCreateOptions } = usePage<{
        currentOrganization: { uuid: string; name: string } | null;
        residentCreateOptions: ResidentCreateOptions;
    }>().props;
    const residentProperties = residentCreateOptions.properties.data;
    const residentUnits = residentCreateOptions.availableUnits.data;
    const form = useForm<CreateResidentFormData>({
        name: '',
        email: '',
        phone: '',
        property_id: '',
        unit_id: '',
    });

    const selectedProperty = useMemo(() => {
        return (
            residentProperties.find(
                (property) => property.id === form.data.property_id,
            ) ?? null
        );
    }, [form.data.property_id, residentProperties]);

    const availableUnits = useMemo(() => {
        if (!selectedProperty) {
            return [];
        }

        return residentUnits.filter(
            (unit) => unit.property_id === selectedProperty.id,
        );
    }, [residentUnits, selectedProperty]);

    const propertyMessage = selectedProperty
        ? selectedProperty.available_units_count === 0
            ? 'No available units are left for this property.'
            : `${selectedProperty.available_units_count ?? 0} of ${
                  selectedProperty.units_count ?? 0
              } units available in this property.`
        : 'Choose a property to view the available units.';

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!currentOrganization) {
            return;
        }

        form.submit(store(currentOrganization.uuid), {
            only,
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
                    disabled={
                        currentOrganization === null ||
                        residentProperties.length === 0
                    }
                >
                    <Plus />
                    Create resident
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create resident</DialogTitle>
                    <DialogDescription>
                        Add a resident user and assign them to an available
                        unit.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={submit} className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="resident-name">Name</Label>
                        <Input
                            id="resident-name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            autoComplete="name"
                            aria-invalid={Boolean(form.errors.name)}
                            aria-describedby={
                                form.errors.name
                                    ? 'resident-name-error'
                                    : undefined
                            }
                            required
                        />
                        <InputError
                            id="resident-name-error"
                            message={form.errors.name}
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="resident-email">Email</Label>
                            <Input
                                id="resident-email"
                                type="email"
                                value={form.data.email}
                                onChange={(event) =>
                                    form.setData('email', event.target.value)
                                }
                                autoComplete="email"
                                aria-invalid={Boolean(form.errors.email)}
                                aria-describedby={
                                    form.errors.email
                                        ? 'resident-email-error'
                                        : undefined
                                }
                                required
                            />
                            <InputError
                                id="resident-email-error"
                                message={form.errors.email}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="resident-phone">Phone</Label>
                            <Input
                                id="resident-phone"
                                value={form.data.phone}
                                onChange={(event) =>
                                    form.setData('phone', event.target.value)
                                }
                                autoComplete="tel"
                                aria-invalid={Boolean(form.errors.phone)}
                                aria-describedby={
                                    form.errors.phone
                                        ? 'resident-phone-error'
                                        : undefined
                                }
                            />
                            <InputError
                                id="resident-phone-error"
                                message={form.errors.phone}
                            />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="resident-property">Property</Label>
                        <Select
                            value={
                                form.data.property_id === ''
                                    ? ''
                                    : String(form.data.property_id)
                            }
                            onValueChange={(value) => {
                                form.setData('property_id', Number(value));
                                form.setData('unit_id', '');
                                form.clearErrors('unit_id');
                            }}
                            required
                        >
                            <SelectTrigger
                                id="resident-property"
                                className="w-full"
                                aria-invalid={Boolean(form.errors.property_id)}
                                aria-describedby={
                                    form.errors.property_id
                                        ? 'resident-property-error'
                                        : undefined
                                }
                            >
                                <SelectValue placeholder="Select a property" />
                            </SelectTrigger>
                            <SelectContent>
                                {residentProperties.map(
                                    (property) => (
                                        <SelectItem
                                            key={property.id}
                                            value={String(property.id)}
                                        >
                                            {property.name}{' '}
                                            <span className="text-muted-foreground">
                                                (
                                                {property.available_units_count ??
                                                    0}
                                                /
                                                {property.units_count ?? 0}
                                                )
                                            </span>
                                        </SelectItem>
                                    ),
                                )}
                            </SelectContent>
                        </Select>
                        <InputError
                            id="resident-property-error"
                            message={form.errors.property_id}
                        />
                        <p
                            className={
                                selectedProperty?.available_units_count === 0
                                    ? 'text-sm text-destructive'
                                    : 'text-sm text-muted-foreground'
                            }
                        >
                            {propertyMessage}
                        </p>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="resident-unit">Unit</Label>
                        <Select
                            value={
                                form.data.unit_id === ''
                                    ? ''
                                    : String(form.data.unit_id)
                            }
                            onValueChange={(value) =>
                                form.setData('unit_id', Number(value))
                            }
                            required
                            disabled={
                                selectedProperty === null ||
                                availableUnits.length === 0
                            }
                        >
                            <SelectTrigger
                                id="resident-unit"
                                className="w-full"
                                aria-invalid={Boolean(form.errors.unit_id)}
                                aria-describedby={
                                    form.errors.unit_id
                                        ? 'resident-unit-error'
                                        : undefined
                                }
                            >
                                <SelectValue
                                    placeholder={
                                        selectedProperty === null
                                            ? 'Select a property first'
                                            : availableUnits.length === 0
                                              ? 'No available units'
                                              : 'Select a unit'
                                    }
                                />
                            </SelectTrigger>
                            <SelectContent>
                                {availableUnits.map((unit) => (
                                    <SelectItem
                                        key={unit.id}
                                        value={String(unit.id)}
                                    >
                                        {unit.name}
                                        {unit.floor
                                            ? ` · Floor ${unit.floor}`
                                            : ''}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError
                            id="resident-unit-error"
                            message={form.errors.unit_id}
                        />
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
                                form.data.unit_id === ''
                            }
                        >
                            {form.processing && <Spinner />}
                            {form.processing
                                ? 'Creating resident'
                                : 'Create resident'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
