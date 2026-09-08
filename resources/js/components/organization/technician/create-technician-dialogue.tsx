import { useForm, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import { store } from '@/wayfinder/App/Http/Controllers/Organization/TechniciansController';

export type TechnicianSpecialtyOption = {
    label: string;
    value: string;
};

type CreateTechnicianFormData = {
    name: string;
    email: string;
    phone: string;
    specialty: string;
    is_available: boolean;
};

type CreateTechnicianDialogueProps = {
    specialties: TechnicianSpecialtyOption[];
    only: Array<'technicians'>;
};

export default function CreateTechnicianDialogue({
    specialties,
    only,
}: CreateTechnicianDialogueProps) {
    const [open, setOpen] = useState(false);
    const { currentOrganization } = usePage<{
        currentOrganization: { uuid: string; name: string } | null;
    }>().props;
    const form = useForm<CreateTechnicianFormData>({
        name: '',
        email: '',
        phone: '',
        specialty: '',
        is_available: true,
    });

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
                    disabled={currentOrganization === null}
                >
                    <Plus />
                    Create technician
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create technician</DialogTitle>
                    <DialogDescription>
                        Add a technician account and assign their specialty.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={submit} className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="technician-name">Name</Label>
                        <Input
                            id="technician-name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            autoComplete="name"
                            aria-invalid={Boolean(form.errors.name)}
                            aria-describedby={
                                form.errors.name
                                    ? 'technician-name-error'
                                    : undefined
                            }
                            required
                        />
                        <InputError
                            id="technician-name-error"
                            message={form.errors.name}
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="technician-email">Email</Label>
                            <Input
                                id="technician-email"
                                type="email"
                                value={form.data.email}
                                onChange={(event) =>
                                    form.setData('email', event.target.value)
                                }
                                autoComplete="email"
                                aria-invalid={Boolean(form.errors.email)}
                                aria-describedby={
                                    form.errors.email
                                        ? 'technician-email-error'
                                        : undefined
                                }
                                required
                            />
                            <InputError
                                id="technician-email-error"
                                message={form.errors.email}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="technician-phone">Phone</Label>
                            <Input
                                id="technician-phone"
                                type="tel"
                                value={form.data.phone}
                                onChange={(event) =>
                                    form.setData('phone', event.target.value)
                                }
                                autoComplete="tel"
                                aria-invalid={Boolean(form.errors.phone)}
                                aria-describedby={
                                    form.errors.phone
                                        ? 'technician-phone-error'
                                        : undefined
                                }
                            />
                            <InputError
                                id="technician-phone-error"
                                message={form.errors.phone}
                            />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="technician-specialty">Specialty</Label>
                        <Select
                            value={form.data.specialty}
                            onValueChange={(value) =>
                                form.setData('specialty', value)
                            }
                            required
                        >
                            <SelectTrigger
                                id="technician-specialty"
                                className="w-full"
                                aria-invalid={Boolean(form.errors.specialty)}
                                aria-describedby={
                                    form.errors.specialty
                                        ? 'technician-specialty-error'
                                        : undefined
                                }
                            >
                                <SelectValue placeholder="Select a specialty" />
                            </SelectTrigger>
                            <SelectContent>
                                {specialties.map((specialty) => (
                                    <SelectItem
                                        key={specialty.value}
                                        value={specialty.value}
                                    >
                                        {specialty.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError
                            id="technician-specialty-error"
                            message={form.errors.specialty}
                        />
                    </div>

                    <div className="flex items-center gap-2">
                        <Checkbox
                            id="technician-is-available"
                            checked={form.data.is_available}
                            onCheckedChange={(checked) =>
                                form.setData('is_available', checked === true)
                            }
                        />
                        <Label htmlFor="technician-is-available">
                            Available for jobs
                        </Label>
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
                                form.data.specialty === ''
                            }
                        >
                            {form.processing && <Spinner />}
                            {form.processing
                                ? 'Creating technician'
                                : 'Create technician'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
