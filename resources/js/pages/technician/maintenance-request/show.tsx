import { Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import {
    ArrowLeft,
    Building2,
    Clock3,
    DollarSign,
    Image,
    Mail,
    MapPin,
    MessageSquare,
    Phone,
    UserRound,
} from 'lucide-react';
import type { DoorOpen } from 'lucide-react';
import { useState } from 'react';
import { ImageModal } from '@/components/organization/maintenance-request/image-modal';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index } from '@/wayfinder/App/Http/Controllers/Technician/MaintenanceRequestsController';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Technician.MaintenanceRequest.Show;
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
type JobData = {
    id: number;
    title: string;
    description: string;
    property: { name: string };
    unit: { name: string };
    resident: { name: string; email: string | null; phone: string | null };
    category: Option;
    priority: Option;
    status: Option;
    completion_notes: string | null;
    actual_cost: string | null;
    completed_at: string | null;
    created_at: string | null;
    issue_images: { data: ImageAttachment[] };
    completion_images: { data: ImageAttachment[] };
    status_logs: { data: StatusLog[] };
};

export default function Show(props: GeneratedPageProps) {
    const maintenanceRequest = props.maintenanceRequest as unknown as {
        data: JobData;
    };
    const job = maintenanceRequest.data;
    const [selectedImage, setSelectedImage] = useState<ImageAttachment | null>(
        null,
    );

    return (
        <>
            <Head title={job.title} />

            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6">
                    <Button variant="ghost" className="w-fit" asChild>
                        <Link href={index().url}>
                            <ArrowLeft />
                            Back to My Jobs
                        </Link>
                    </Button>

                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div className="space-y-2">
                            <h1 className="text-3xl font-semibold tracking-tight">
                                {job.title}
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Job #{job.id} · Reported{' '}
                                {job.created_at
                                    ? format(job.created_at, 'MMM d, yyyy')
                                    : '—'}
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Badge variant="outline" className="px-3 py-1">
                                {job.status.label}
                            </Badge>
                            <Badge variant="outline" className="px-3 py-1">
                                {job.priority.label}
                            </Badge>
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
                                        {job.description}
                                    </p>
                                </CardContent>
                            </Card>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <DetailCard
                                    icon={MapPin}
                                    title="Location"
                                    value={`${job.property.name} · Unit ${job.unit.name}`}
                                />
                                <DetailCard
                                    icon={Building2}
                                    title="Category"
                                    value={job.category.label}
                                />
                            </div>

                            <PhotoSection
                                title="Issue photos"
                                photos={job.issue_images.data}
                                onSelect={setSelectedImage}
                            />

                            {(job.completion_notes ||
                                job.actual_cost ||
                                job.completed_at) && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>
                                            Completion details
                                        </CardTitle>
                                        {job.completed_at && (
                                            <CardDescription>
                                                Completed{' '}
                                                {format(
                                                    job.completed_at,
                                                    'MMM d, yyyy',
                                                )}
                                            </CardDescription>
                                        )}
                                    </CardHeader>
                                    <CardContent className="grid gap-4">
                                        {job.completion_notes && (
                                            <p className="text-sm whitespace-pre-wrap text-muted-foreground">
                                                {job.completion_notes}
                                            </p>
                                        )}
                                        {job.actual_cost && (
                                            <p className="flex items-center gap-2 text-sm font-medium">
                                                <DollarSign className="size-4 text-muted-foreground" />
                                                Actual cost: ${job.actual_cost}
                                            </p>
                                        )}
                                    </CardContent>
                                </Card>
                            )}

                            <PhotoSection
                                title="Completion photos"
                                photos={job.completion_images.data}
                                onSelect={setSelectedImage}
                            />
                        </div>

                        <div className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <UserRound className="size-5" />
                                        Resident contact
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="grid gap-3">
                                    <p className="text-sm font-medium">
                                        {job.resident.name}
                                    </p>
                                    {job.resident.email && (
                                        <a
                                            href={`mailto:${job.resident.email}`}
                                            className="inline-flex items-center gap-2 text-sm break-all text-primary hover:text-primary/80"
                                        >
                                            <Mail className="size-4" />
                                            {job.resident.email}
                                        </a>
                                    )}
                                    {job.resident.phone && (
                                        <a
                                            href={`tel:${job.resident.phone}`}
                                            className="inline-flex items-center gap-2 text-sm text-primary hover:text-primary/80"
                                        >
                                            <Phone className="size-4" />
                                            {job.resident.phone}
                                        </a>
                                    )}
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Clock3 className="size-5" />
                                        Status timeline
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="grid gap-5">
                                    {job.status_logs.data.length === 0 ? (
                                        <p className="text-sm text-muted-foreground">
                                            No status updates yet.
                                        </p>
                                    ) : (
                                        job.status_logs.data.map((log) => (
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

            {selectedImage && (
                <ImageModal
                    isOpen
                    onClose={() => setSelectedImage(null)}
                    imageUrl={selectedImage.file_path}
                    imageSrcSet={selectedImage.srcset}
                    imageName={selectedImage.original_name}
                />
            )}
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
    onSelect,
}: {
    title: string;
    photos: ImageAttachment[];
    onSelect: (photo: ImageAttachment) => void;
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
                            <button
                                key={photo.id}
                                type="button"
                                className="overflow-hidden rounded-lg border text-left"
                                onClick={() => onSelect(photo)}
                            >
                                <img
                                    src={photo.file_path}
                                    srcSet={photo.srcset ?? undefined}
                                    alt={photo.original_name}
                                    className="aspect-square w-full object-cover"
                                />
                            </button>
                        ))}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

Show.layout = {
    breadcrumbs: [{ title: 'My Jobs', href: index().url }],
};
