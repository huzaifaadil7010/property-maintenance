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
import PropertyType from '@/wayfinder/App/Enums/PropertyType';
import { store } from '@/wayfinder/App/Http/Controllers/Organization/PropertiesController';

type PropertyTypeValue = (typeof PropertyType)[keyof typeof PropertyType];

type CreatePropertyFormData = {
    name: string;
    type: PropertyTypeValue;
    address: string;
    city: string;
};

export default function CreatePropertyDialogue() {
    const [open, setOpen] = useState(false);
    const { currentOrganization } = usePage().props;
    const form = useForm<CreatePropertyFormData>({
        name: '',
        type: PropertyType.APARTMENT,
        address: '',
        city: '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!currentOrganization) {
            return;
        }

        form.submit(store(currentOrganization.uuid), {
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
                <Button className="w-full sm:w-auto">
                    <Plus />
                    Create property
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create property</DialogTitle>
                    <DialogDescription>
                        Add a property to your current organization.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={submit} className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="property-name">Name</Label>
                        <Input
                            id="property-name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            placeholder="Green View Apartments"
                            autoComplete="organization"
                            aria-invalid={Boolean(form.errors.name)}
                            aria-describedby={
                                form.errors.name
                                    ? 'property-name-error'
                                    : undefined
                            }
                            required
                        />
                        <InputError
                            id="property-name-error"
                            message={form.errors.name}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="property-type">Type</Label>
                        <Select
                            value={form.data.type}
                            onValueChange={(value) =>
                                form.setData('type', value as PropertyTypeValue)
                            }
                        >
                            <SelectTrigger
                                id="property-type"
                                className="w-full"
                                aria-invalid={Boolean(form.errors.type)}
                                aria-describedby={
                                    form.errors.type
                                        ? 'property-type-error'
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
                            id="property-type-error"
                            message={form.errors.type}
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="property-address">Address</Label>
                            <Input
                                id="property-address"
                                value={form.data.address}
                                onChange={(event) =>
                                    form.setData('address', event.target.value)
                                }
                                placeholder="12 Garden Road"
                                autoComplete="street-address"
                                aria-invalid={Boolean(form.errors.address)}
                                aria-describedby={
                                    form.errors.address
                                        ? 'property-address-error'
                                        : undefined
                                }
                                required
                            />
                            <InputError
                                id="property-address-error"
                                message={form.errors.address}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="property-city">City</Label>
                            <Input
                                id="property-city"
                                value={form.data.city}
                                onChange={(event) =>
                                    form.setData('city', event.target.value)
                                }
                                placeholder="Lahore"
                                autoComplete="address-level2"
                                aria-invalid={Boolean(form.errors.city)}
                                aria-describedby={
                                    form.errors.city
                                        ? 'property-city-error'
                                        : undefined
                                }
                                required
                            />
                            <InputError
                                id="property-city-error"
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
                                ? 'Creating property'
                                : 'Create property'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
