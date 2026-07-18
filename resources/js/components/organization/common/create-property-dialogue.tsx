import { useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';

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
import PropertyType from '@/wayfinder/App/Enums/PropertyType';

type PropertyTypeValue = (typeof PropertyType)[keyof typeof PropertyType];

type CreatePropertyFormData = {
    name: string;
    type: PropertyTypeValue;
    address: string;
    city: string;
};

export default function CreatePropertyDialogue() {
    const form = useForm<CreatePropertyFormData>({
        name: '',
        type: PropertyType.APARTMENT,
        address: '',
        city: '',
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
    }

    return (
        <Dialog>
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
                        Add the property details below. Saving will be connected
                        in a later step.
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
                            required
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
                            >
                                <SelectValue placeholder="Select a type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={PropertyType.APARTMENT}>
                                    Apartment
                                </SelectItem>
                            </SelectContent>
                        </Select>
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
                                required
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
                                required
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => form.reset()}
                            >
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button type="submit">Create property</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
