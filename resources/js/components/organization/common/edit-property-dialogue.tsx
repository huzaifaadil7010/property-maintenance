import { useForm, usePage } from '@inertiajs/react';
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
import PropertyType from '@/wayfinder/App/Enums/PropertyType';
import { update } from '@/wayfinder/App/Http/Controllers/Organization/PropertiesController';

type PropertyTypeValue = (typeof PropertyType)[keyof typeof PropertyType];

type EditPropertyFormData = {
    name: string;
    type: PropertyTypeValue;
    address: string;
    city: string;
};

export type EditableProperty = {
    id: string | number;
    name: string;
    type: {
        label: string;
        value: PropertyTypeValue;
    };
    address: string;
    city: string;
};

type EditPropertyDialogueProps = {
    property: EditableProperty;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function EditPropertyDialogue({
    property,
    open,
    onOpenChange,
}: EditPropertyDialogueProps) {
    const { currentOrganization } = usePage().props;
    const form = useForm<EditPropertyFormData>({
        name: property.name,
        type: property.type.value,
        address: property.address,
        city: property.city,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!currentOrganization) {
            return;
        }

        form.submit(
            update({
                organization: currentOrganization.uuid,
                property: property.id,
            }),
            {
                only: ['properties'],
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
                    <DialogTitle>Edit property</DialogTitle>
                    <DialogDescription>
                        Update the property details below.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={submit} className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="edit-property-name">Name</Label>
                        <Input
                            id="edit-property-name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            autoComplete="organization"
                            aria-invalid={Boolean(form.errors.name)}
                            aria-describedby={
                                form.errors.name
                                    ? 'edit-property-name-error'
                                    : undefined
                            }
                            required
                        />
                        <InputError
                            id="edit-property-name-error"
                            message={form.errors.name}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="edit-property-type">Type</Label>
                        <Select
                            value={form.data.type}
                            onValueChange={(value) =>
                                form.setData('type', value as PropertyTypeValue)
                            }
                        >
                            <SelectTrigger
                                id="edit-property-type"
                                className="w-full"
                                aria-invalid={Boolean(form.errors.type)}
                                aria-describedby={
                                    form.errors.type
                                        ? 'edit-property-type-error'
                                        : undefined
                                }
                            >
                                <SelectValue placeholder="Select a type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={PropertyType.APARTMENT}>
                                    Apartment
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError
                            id="edit-property-type-error"
                            message={form.errors.type}
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="edit-property-address">
                                Address
                            </Label>
                            <Input
                                id="edit-property-address"
                                value={form.data.address}
                                onChange={(event) =>
                                    form.setData('address', event.target.value)
                                }
                                autoComplete="street-address"
                                aria-invalid={Boolean(form.errors.address)}
                                aria-describedby={
                                    form.errors.address
                                        ? 'edit-property-address-error'
                                        : undefined
                                }
                                required
                            />
                            <InputError
                                id="edit-property-address-error"
                                message={form.errors.address}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="edit-property-city">City</Label>
                            <Input
                                id="edit-property-city"
                                value={form.data.city}
                                onChange={(event) =>
                                    form.setData('city', event.target.value)
                                }
                                autoComplete="address-level2"
                                aria-invalid={Boolean(form.errors.city)}
                                aria-describedby={
                                    form.errors.city
                                        ? 'edit-property-city-error'
                                        : undefined
                                }
                                required
                            />
                            <InputError
                                id="edit-property-city-error"
                                message={form.errors.city}
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
                                form.processing || currentOrganization === null
                            }
                        >
                            {form.processing && <Spinner />}
                            {form.processing
                                ? 'Updating property'
                                : 'Update property'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
