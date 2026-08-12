import { useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { toast } from 'sonner';
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
import MaintenanceCategory from '@/wayfinder/App/Enums/MaintenanceCategory';
import MaintenancePriority from '@/wayfinder/App/Enums/MaintenancePriority';
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
    const [open, setOpen] = useState(false);
    const form = useForm<FormData>({
        title: '',
        category: MaintenanceCategory.GENERAL,
        priority: MaintenancePriority.NORMAL,
        description: '',
    });
    const cannotSubmitError = (
        form.errors as Record<string, string | undefined>
    ).cannot_submit;

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        form.submit(store(), {
            only,
            preserveScroll: true,
            onSuccess: () => {
                form.resetAndClearErrors();
                setOpen(false);
            },
            onError: (errors) => {
                if (errors.cannot_submit) {
                    toast.error(errors.cannot_submit);
                }
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
                    Report an issue
                </Button>
            </DialogTrigger>

            <DialogContent>
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
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Spinner />}
                            {form.processing
                                ? 'Reporting issue'
                                : 'Report issue'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
