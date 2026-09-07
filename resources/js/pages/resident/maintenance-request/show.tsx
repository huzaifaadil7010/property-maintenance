import { Head, Link, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import {
    ArrowLeft,
    Building2,
    CheckCircle2,
    Clock3,
    DoorOpen,
    Image,
    MapPin,
    MessageSquare,
    RotateCcw,
    UserRound,
    Wrench,
} from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import ConfirmationDialogue from '@/components/organization/common/confirmation-dialogue';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import MaintenanceRequestStatus from '@/wayfinder/App/Enums/MaintenanceRequestStatus';
import ConfirmMaintenanceRequestResolutionController from '@/wayfinder/App/Http/Controllers/Resident/ConfirmMaintenanceRequestResolutionController';
import ReopenMaintenanceRequestController from '@/wayfinder/App/Http/Controllers/Resident/ReopenMaintenanceRequestController';
import { index } from '@/wayfinder/App/Http/Controllers/Resident/MaintenanceRequestsController';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Resident.MaintenanceRequest.Show;
type Option = { label: string; value: string };
type ImageAttachment = {
    id: number;
    file_path: string;
    srcset: string | null;
    original_name: string;
};
type StatusLog = {
    id: number;
    from_status: Option | null;
    to_status: Option | null;
    notes: string | null;
    changed_by: { name: string };
    created_at: string | null;
};
type RequestData = {
    id: number;
    title: string;
    description: string;
    property: { name: string };
    unit: { name: string };
    assigned_technician: { name: string } | null;
    category: Option;
    priority: Option;
    status: Option;
    completion_notes: string | null;
    completed_at: string | null;
    closed_at: string | null;
    created_at: string | null;
    issue_images: { data: ImageAttachment[] };
    completion_images: { data: ImageAttachment[] };
    status_logs: { data: StatusLog[] };
};

export default function Show(props: GeneratedPageProps) {
    const maintenanceRequest = props.maintenanceRequest as unknown as {
        data: RequestData;
    };
    const request = maintenanceRequest.data;
    const [confirmOpen, setConfirmOpen] = useState(false);
    const [reopenOpen, setReopenOpen] = useState(false);
    const confirmForm = useForm({
        status: MaintenanceRequestStatus.CLOSED,
        notes: '',
    });
    const reopenForm = useForm({
        status: MaintenanceRequestStatus.REOPENED,
        notes: '',
    });
    const isCompleted =
        request.status.value === MaintenanceRequestStatus.COMPLETED;

    function submitConfirmation(): void {
        confirmForm.submit(
            ConfirmMaintenanceRequestResolutionController(request.id),
            {
                only: ['maintenanceRequest'],
                preserveScroll: true,
                onSuccess: () => {
                    confirmForm.resetAndClearErrors();
                    setConfirmOpen(false);
                },
                onError: (errors) => {
                    if (errors.cannot_submit) toast.error(errors.cannot_submit);
                },
            },
        );
    }

    function submitReopen(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        reopenForm.submit(ReopenMaintenanceRequestController(request.id), {
            only: ['maintenanceRequest'],
            preserveScroll: true,
            onSuccess: () => {
                reopenForm.resetAndClearErrors();
                setReopenOpen(false);
            },
            onError: (errors) => {
                if (errors.cannot_submit) toast.error(errors.cannot_submit);
            },
        });
    }

    return (
        <>
            <Head title={request.title} />
            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6">
                    <Button variant="ghost" className="w-fit" asChild>
                        <Link href={index().url}>
                            <ArrowLeft />
                            Back to My Requests
                        </Link>
                    </Button>
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div className="space-y-2">
                            <h1 className="text-3xl font-semibold tracking-tight">
                                {request.title}
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Request #{request.id} · Reported{' '}
                                {request.created_at
                                    ? format(request.created_at, 'MMM d, yyyy')
                                    : '—'}
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Badge variant="outline" className="px-3 py-1">
                                {request.status.label}
                            </Badge>
                            <Badge variant="outline" className="px-3 py-1">
                                {request.priority.label}
                            </Badge>
                            {isCompleted && (
                                <>
                                    <Button
                                        onClick={() => setConfirmOpen(true)}
                                    >
                                        <CheckCircle2 />
                                        Confirm Resolution
                                    </Button>
                                    <Button
                                        variant="outline"
                                        onClick={() => setReopenOpen(true)}
                                    >
                                        <RotateCcw />
                                        Reopen Issue
                                    </Button>
                                </>
                            )}
                        </div>
                    </div>
                    <div className="grid gap-6 lg:grid-cols-3">
                        <div className="space-y-6 lg:col-span-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <MessageSquare className="size-5" />
                                        Description
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm leading-relaxed whitespace-pre-wrap text-muted-foreground">
                                        {request.description}
                                    </p>
                                </CardContent>
                            </Card>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <DetailCard
                                    icon={MapPin}
                                    title="Location"
                                    value={`${request.property.name} · Unit ${request.unit.name}`}
                                />
                                <DetailCard
                                    icon={Building2}
                                    title="Category"
                                    value={request.category.label}
                                />
                            </div>
                            <PhotoSection
                                title="Issue photos"
                                photos={request.issue_images.data}
                            />
                            <PhotoSection
                                title="Completion photos"
                                photos={request.completion_images.data}
                            />
                            {request.completion_notes && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Completion notes</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                                            {request.completion_notes}
                                        </p>
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                        <div className="space-y-6">
                            <DetailCard
                                icon={Wrench}
                                title="Assigned technician"
                                value={
                                    request.assigned_technician?.name ??
                                    'Not assigned yet'
                                }
                            />
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Clock3 className="size-5" />
                                        Status timeline
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="grid gap-5">
                                    {request.status_logs.data.length === 0 ? (
                                        <p className="text-sm text-muted-foreground">
                                            No status updates yet.
                                        </p>
                                    ) : (
                                        request.status_logs.data.map((log) => (
                                            <div
                                                key={log.id}
                                                className="grid gap-1 border-l-2 pl-3"
                                            >
                                                <p className="text-sm font-medium">
                                                    {log.from_status?.label ??
                                                        'Created'}{' '}
                                                    →{' '}
                                                    {log.to_status?.label ??
                                                        '—'}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {log.changed_by.name}
                                                    {log.created_at
                                                        ? ` · ${format(log.created_at, 'MMM d, yyyy')}`
                                                        : ''}
                                                </p>
                                                {log.notes && (
                                                    <p className="text-sm text-muted-foreground">
                                                        {log.notes}
                                                    </p>
                                                )}
                                            </div>
                                        ))
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
            <ConfirmationDialogue
                open={confirmOpen}
                onOpenChange={setConfirmOpen}
                onSubmit={submitConfirmation}
                title="Confirm resolution"
                description="This will mark the request as closed."
                submitLabel="Confirm resolution"
                submittingLabel="Confirming"
                processing={confirmForm.processing}
            />
            <Dialog open={reopenOpen} onOpenChange={setReopenOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Reopen issue</DialogTitle>
                        <DialogDescription>
                            Tell your property manager why the issue is not
                            resolved.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitReopen} className="grid gap-5">
                        <div className="grid gap-2">
                            <Label htmlFor="reopen-notes">Reason</Label>
                            <textarea
                                id="reopen-notes"
                                value={reopenForm.data.notes}
                                onChange={(event) =>
                                    reopenForm.setData(
                                        'notes',
                                        event.target.value,
                                    )
                                }
                                className="min-h-28 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                required
                            />
                            <InputError message={reopenForm.errors.notes} />
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => setReopenOpen(false)}
                                disabled={reopenForm.processing}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={reopenForm.processing}
                            >
                                {reopenForm.processing && <Spinner />}
                                {reopenForm.processing
                                    ? 'Reopening'
                                    : 'Reopen issue'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}

function DetailCard({
    icon: Icon,
    title,
    value,
}: {
    icon: typeof DoorOpen;
    title: string;
    value: string;
}) {
    return (
        <Card>
            <CardHeader className="flex-row items-center gap-3">
                <Icon className="size-5 text-muted-foreground" />
                <div className="grid gap-1">
                    <CardDescription>{title}</CardDescription>
                    <CardTitle className="text-base">{value}</CardTitle>
                </div>
            </CardHeader>
        </Card>
    );
}
function PhotoSection({
    title,
    photos,
}: {
    title: string;
    photos: ImageAttachment[];
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <Image className="size-5" />
                    {title}
                </CardTitle>
            </CardHeader>
            <CardContent>
                {photos.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        No {title.toLowerCase()} available.
                    </p>
                ) : (
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        {photos.map((photo) => (
                            <a
                                key={photo.id}
                                href={photo.file_path}
                                target="_blank"
                                rel="noreferrer"
                            >
                                <img
                                    src={photo.file_path}
                                    srcSet={photo.srcset ?? undefined}
                                    alt={photo.original_name}
                                    className="aspect-square w-full rounded-lg border object-cover"
                                />
                            </a>
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

Show.layout = {
    breadcrumbs: [{ title: 'My Requests', href: index().url }],
};
