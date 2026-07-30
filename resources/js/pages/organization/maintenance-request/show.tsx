import { Head, useForm, usePage } from '@inertiajs/react';
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Clock,
    DollarSign,
    Image as ImageIcon,
    MapPin,
    MessageSquare,
    Phone,
    RotateCcw,
    User,
    UserCog,
    Wrench,
} from 'lucide-react';
import { format } from 'date-fns';
import { useEffect, useState, type FormEvent } from 'react';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { ImageModal } from '@/components/organization/maintenance-request/image-modal';
import AssignTechnicianDialog, {
    type AssignTechnicianFormData,
    type TechnicianOption,
} from '@/components/organization/maintenance-request/assign-technician-dialog';
import StatusUpdateDialog, {
    type MaintenanceRequestStatusOption,
    type StatusUpdateFormData,
} from '@/components/organization/maintenance-request/status-update-dialog';
import AssignMaintenanceRequestTechnicianController from '@/wayfinder/App/Http/Controllers/Organization/AssignMaintenanceRequestTechnicianController';
import UpdateMaintenanceRequestStatusController from '@/wayfinder/App/Http/Controllers/Organization/UpdateMaintenanceRequestStatusController';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.MaintenanceRequest.Show;

type Attachment = {
    id: number;
    file_path: string;
    original_name: string;
    mime_type: string;
    size: number;
    type: {
        label: string;
        value: string;
    };
    uploader: {
        id: number;
        name: string;
    };
    created_at: string;
};

type StatusLog = {
    id: number;
    from_status: {
        label: string;
        value: string;
    } | null;
    to_status: {
        label: string;
        value: string;
    } | null;
    notes: string | null;
    changed_by: {
        id: number;
        name: string;
    };
    created_at: string;
};

const getStatusColor = (status: string | null | undefined): string => {
    if (!status) return 'border bg-muted text-muted-foreground';
    const colors: Record<string, string> = {
        open: 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400',
        assigned: 'border-blue-500/30 bg-blue-500/10 text-blue-600 dark:text-blue-400',
        'in-progress': 'border-violet-500/30 bg-violet-500/10 text-violet-600 dark:text-violet-400',
        completed: 'border-green-500/30 bg-green-500/10 text-green-600 dark:text-green-400',
        closed: 'border-slate-500/30 bg-slate-500/10 text-slate-600 dark:text-slate-400',
        reopened: 'border-rose-500/30 bg-rose-500/10 text-rose-600 dark:text-rose-400',
    };
    return colors[status.toLowerCase()] ?? 'border bg-muted text-muted-foreground';
};

const getStatusDotColor = (status: string | null | undefined): string => {
    if (!status) return 'bg-muted-foreground';
    const colors: Record<string, string> = {
        open: 'bg-amber-500',
        assigned: 'bg-blue-500',
        'in-progress': 'bg-violet-500',
        completed: 'bg-green-500',
        closed: 'bg-slate-400',
        reopened: 'bg-rose-500',
    };
    return colors[status.toLowerCase()] ?? 'bg-muted-foreground';
};

const getPriorityColor = (priority: string | null | undefined): string => {
    if (!priority) return 'border bg-muted text-muted-foreground';
    const colors: Record<string, string> = {
        urgent: 'border-rose-500/30 bg-rose-500/10 text-rose-600 dark:text-rose-400',
        high: 'border-orange-500/30 bg-orange-500/10 text-orange-600 dark:text-orange-400',
        normal: 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400',
        low: 'border-green-500/30 bg-green-500/10 text-green-600 dark:text-green-400',
    };
    return colors[priority.toLowerCase()] ?? 'border bg-muted text-muted-foreground';
};

const getStatusIcon = (status: string | null | undefined) => {
    const icons: Record<string, JSX.Element> = {
        open: <AlertCircle className="h-4 w-4" />,
        assigned: <UserCog className="h-4 w-4" />,
        'in-progress': <Clock className="h-4 w-4" />,
        completed: <CheckCircle2 className="h-4 w-4" />,
        closed: <CheckCircle2 className="h-4 w-4" />,
        reopened: <RotateCcw className="h-4 w-4" />,
    };

    if (!status) return <AlertCircle className="h-4 w-4" />;

    return icons[status.toLowerCase()] || <AlertCircle className="h-4 w-4" />;
};

const isImageFile = (mimeType: string): boolean => {
    return mimeType.startsWith('image/');
};

const formatBytes = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
};

export default function Show({
    maintenanceRequest,
    maintenanceRequestStatuses,
    availableTechnicians,
}: GeneratedPageProps) {
    const [selectedImage, setSelectedImage] = useState<{
        url: string;
        name: string;
    } | null>(null);
    const [assignDialogOpen, setAssignDialogOpen] = useState(false);
    const [statusDialogOpen, setStatusDialogOpen] = useState(false);

    const imageAttachments = (maintenanceRequest.data.attachments || []).filter(
        (attachment) => isImageFile(attachment.mime_type),
    );

    const statuses = (maintenanceRequestStatuses ||
        []) as MaintenanceRequestStatusOption[];
    const assignedTechnician = maintenanceRequest.data.assigned_technician;
    const hasAssignedTechnician = Boolean(assignedTechnician);

    const baseTechnicians = availableTechnicians as
        | TechnicianOption[]
        | undefined;
    const technicians =
        baseTechnicians && assignedTechnician
            ? baseTechnicians.some(
                  (technician) => technician.id === assignedTechnician.id,
              )
                ? baseTechnicians
                : [
                      ...baseTechnicians,
                      {
                          id: assignedTechnician.id,
                          name: assignedTechnician.name,
                      },
                  ]
            : baseTechnicians;

    const currentStatusValue = maintenanceRequest.data.status?.value ?? '';
    const { currentOrganization } = usePage().props;

    const assignForm = useForm<AssignTechnicianFormData>({
        assigned_technician_id: assignedTechnician?.id ?? '',
        notes: '',
    });
    const statusForm = useForm<StatusUpdateFormData>({
        status: currentStatusValue,
        notes: '',
    });

    useEffect(() => {
        if (assignDialogOpen) {
            assignForm.setData({
                assigned_technician_id: assignedTechnician?.id ?? '',
                notes: '',
            });
        } else {
            assignForm.resetAndClearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [assignDialogOpen, assignedTechnician?.id]);

    useEffect(() => {
        if (statusDialogOpen) {
            statusForm.setData({ status: currentStatusValue, notes: '' });
        } else {
            statusForm.resetAndClearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [statusDialogOpen, currentStatusValue]);

    function submitAssignTechnician(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!currentOrganization) {
            return;
        }

        assignForm.submit(
            AssignMaintenanceRequestTechnicianController({
                organization: currentOrganization.uuid,
                maintenanceRequest: maintenanceRequest.data.id,
            }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    assignForm.resetAndClearErrors();
                    setAssignDialogOpen(false);
                },
                onError: (errors) => {
                    if (errors.cannot_submit) {
                        toast.error(errors.cannot_submit);
                    }
                },
            },
        );
    }

    function submitStatusUpdate(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!currentOrganization) {
            return;
        }

        statusForm.submit(
            UpdateMaintenanceRequestStatusController({
                organization: currentOrganization.uuid,
                maintenanceRequest: maintenanceRequest.data.id,
            }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    statusForm.resetAndClearErrors();
                    setStatusDialogOpen(false);
                },
                onError: (errors) => {
                    if (errors.cannot_submit) {
                        toast.error(errors.cannot_submit);
                    }
                },
            },
        );
    }

    return (
        <>
            <Head title={maintenanceRequest.data.title} />

            <div className="flex min-h-0 flex-1 flex-col p-4 md:p-6 lg:p-8">
                <div className="mx-auto w-full max-w-6xl flex-1">
                    {/* Header Section */}
                    <div className="mb-8">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div className="space-y-2">
                                <h1 className="text-4xl font-bold tracking-tight">
                                    {maintenanceRequest.data.title}
                                </h1>
                                <p className="text-sm text-muted-foreground">
                                    Request ID:{' '}
                                    <span className="font-semibold text-foreground">
                                        #{maintenanceRequest.data.id}
                                    </span>
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <Badge
                                    variant="outline"
                                    className={`flex items-center gap-2 px-4 py-2 text-sm font-semibold ${getStatusColor(maintenanceRequest.data.status?.value)}`}
                                >
                                    {getStatusIcon(
                                        maintenanceRequest.data.status?.value,
                                    )}
                                    {maintenanceRequest.data.status?.label ||
                                        '—'}
                                </Badge>
                                <Badge
                                    variant="outline"
                                    className={`px-4 py-2 text-sm font-semibold ${getPriorityColor(maintenanceRequest.data.priority?.value)}`}
                                >
                                    {maintenanceRequest.data.priority?.label ||
                                        '—'}
                                </Badge>
                                <Button
                                    variant={
                                        hasAssignedTechnician
                                            ? 'outline'
                                            : 'default'
                                    }
                                    onClick={() =>
                                        setAssignDialogOpen(true)
                                    }
                                >
                                    {hasAssignedTechnician
                                        ? 'Reassign Technician'
                                        : 'Assign Technician'}
                                </Button>
                                <Button
                                    variant="outline"
                                    disabled={!hasAssignedTechnician}
                                    title={
                                        hasAssignedTechnician
                                            ? undefined
                                            : 'Assign a technician first'
                                    }
                                    onClick={() =>
                                        setStatusDialogOpen(true)
                                    }
                                >
                                    Update Status
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-3">
                        <div className="space-y-6 lg:col-span-2">
                            {/* Description */}
                            <div className="overflow-hidden rounded-lg border bg-card shadow-sm">
                                <div className="p-6">
                                    <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold">
                                        <MessageSquare className="h-5 w-5 text-muted-foreground" />
                                        Description
                                    </h2>
                                    <p className="text-base leading-relaxed whitespace-pre-wrap text-muted-foreground">
                                        {maintenanceRequest.data.description}
                                    </p>
                                </div>
                            </div>

                            {/* Location & Category Grid */}
                            <div className="grid gap-4 sm:grid-cols-2">
                                {/* Location Card */}
                                <div className="overflow-hidden rounded-lg border bg-card shadow-sm">
                                    <div className="p-6">
                                        <div className="mb-4 flex items-center gap-2">
                                            <MapPin className="h-4 w-4 text-muted-foreground" />
                                            <h3 className="text-sm font-semibold">
                                                Location
                                            </h3>
                                        </div>
                                        <div className="space-y-3">
                                            <div>
                                                <p className="text-xs font-medium text-muted-foreground">
                                                    Property
                                                </p>
                                                <p className="text-sm font-semibold">
                                                    {
                                                        maintenanceRequest
                                                            .data.property
                                                            ?.name
                                                    }
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-xs font-medium text-muted-foreground">
                                                    Unit
                                                </p>
                                                <p className="text-sm font-semibold">
                                                    {
                                                        maintenanceRequest.data
                                                            .unit?.name
                                                    }
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Category Card */}
                                <div className="overflow-hidden rounded-lg border bg-card shadow-sm">
                                    <div className="p-6">
                                        <div className="mb-4 flex items-center gap-2">
                                            <Wrench className="h-4 w-4 text-muted-foreground" />
                                            <h3 className="text-sm font-semibold">
                                                Category
                                            </h3>
                                        </div>
                                        <p className="text-sm font-medium text-muted-foreground">
                                            {maintenanceRequest.data.category
                                                ?.label || '—'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Timeline */}
                            <div className="overflow-hidden rounded-lg border bg-card shadow-sm">
                                <div className="p-6">
                                    <h2 className="mb-6 flex items-center gap-2 text-lg font-semibold">
                                        <Calendar className="h-5 w-5 text-muted-foreground" />
                                        Timeline
                                    </h2>
                                    <div className="space-y-6">
                                        {/* Created */}
                                        <div className="flex gap-4">
                                            <div className="flex flex-col items-center pt-1">
                                                <div className={`h-3 w-3 rounded-full ${getStatusDotColor('open')}`} />
                                                <div className="mt-3 h-12 w-0.5 bg-border" />
                                            </div>
                                            <div className="pb-4">
                                                <p className="text-xs font-medium text-muted-foreground">
                                                    Created
                                                </p>
                                                <p className="mt-2 text-sm font-semibold">
                                                    {maintenanceRequest?.data
                                                        .created_at
                                                        ? format(
                                                              new Date(
                                                                  maintenanceRequest
                                                                      ?.data
                                                                      .created_at,
                                                              ),
                                                              'MMM dd, yyyy • h:mm a',
                                                          )
                                                        : '-'}
                                                </p>
                                            </div>
                                        </div>

                                        {/* Completed */}
                                        {maintenanceRequest.data
                                            .completed_at && (
                                            <div className="flex gap-4">
                                                <div className="flex flex-col items-center pt-1">
                                                    <div className={`h-3 w-3 rounded-full ${getStatusDotColor('completed')}`} />
                                                    <div className="mt-3 h-12 w-0.5 bg-border" />
                                                </div>
                                                <div className="pb-4">
                                                    <p className="text-xs font-medium text-muted-foreground">
                                                        Completed
                                                    </p>
                                                    <p className="mt-2 text-sm font-semibold">
                                                        {format(
                                                            new Date(
                                                                maintenanceRequest
                                                                    .data
                                                                    .completed_at,
                                                            ),
                                                            'MMM dd, yyyy • h:mm a',
                                                        )}
                                                    </p>
                                                </div>
                                            </div>
                                        )}

                                        {/* Last Updated */}
                                        <div className="flex gap-4">
                                            <div className="flex flex-col items-center pt-1">
                                                <div className={`h-3 w-3 rounded-full ${getStatusDotColor(maintenanceRequest.data.status?.value)}`} />
                                            </div>
                                            <div>
                                                <p className="text-xs font-medium text-muted-foreground">
                                                    Last Updated
                                                </p>
                                                <p className="mt-2 text-sm font-semibold">
                                                    {maintenanceRequest?.data
                                                        .updated_at
                                                        ? format(
                                                              new Date(
                                                                  maintenanceRequest
                                                                      ?.data
                                                                      .updated_at,
                                                              ),
                                                              'MMM dd, yyyy • h:mm a',
                                                          )
                                                        : '-'}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Completion Notes */}
                            {maintenanceRequest.data.completion_notes && (
                                <div className="overflow-hidden rounded-lg border bg-card shadow-sm">
                                    <div className="p-6">
                                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold">
                                            <CheckCircle2 className="h-5 w-5 text-muted-foreground" />
                                            Completion Notes
                                        </h2>
                                        <p className="text-base leading-relaxed whitespace-pre-wrap text-muted-foreground">
                                            {
                                                maintenanceRequest.data
                                                    .completion_notes
                                            }
                                        </p>
                                    </div>
                                </div>
                            )}

                            {/* Image Attachments */}
                            {imageAttachments.length > 0 && (
                                <div className="overflow-hidden rounded-lg border bg-card shadow-sm">
                                    <div className="p-6">
                                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold">
                                            <ImageIcon className="h-5 w-5 text-muted-foreground" />
                                            Attachments (
                                            {imageAttachments.length})
                                        </h2>
                                        <div className="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                                            {imageAttachments.map(
                                                (attachment) => (
                                                    <button
                                                        key={attachment.id}
                                                        type="button"
                                                        onClick={() =>
                                                            setSelectedImage({
                                                                url: attachment.file_path,
                                                                name: attachment.original_name,
                                                            })
                                                        }
                                                        className="group relative overflow-hidden rounded-lg border bg-transparent p-0 text-left transition-all hover:bg-transparent hover:shadow-md hover:border-ring/50"
                                                    >
                                                        <div className="aspect-square overflow-hidden bg-muted">
                                                            <img
                                                                src={
                                                                    attachment.file_path
                                                                }
                                                                alt={
                                                                    attachment.original_name
                                                                }
                                                                className="h-full w-full object-cover transition-transform group-hover:scale-105"
                                                            />
                                                        </div>
                                                        <div className="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors group-hover:bg-black/30">
                                                            <div className="opacity-0 transition-opacity group-hover:opacity-100">
                                                                <ImageIcon className="h-6 w-6 text-white" />
                                                            </div>
                                                        </div>
                                                        <div className="bg-card p-3">
                                                            <p className="truncate text-xs font-medium">
                                                                {
                                                                    attachment.original_name
                                                                }
                                                            </p>
                                                            <p className="truncate text-xs text-muted-foreground">
                                                                {formatBytes(
                                                                    attachment.size,
                                                                )}
                                                            </p>
                                                            <p className="mt-1 truncate text-xs text-muted-foreground">
                                                                by{' '}
                                                                {
                                                                    attachment
                                                                        .uploader
                                                                        .name
                                                                }
                                                            </p>
                                                        </div>
                                                    </button>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Status History */}
                            {(maintenanceRequest.data.status_logs || [])
                                .length > 0 && (
                                <div className="overflow-hidden rounded-lg border bg-card shadow-sm">
                                    <div className="p-6">
                                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold">
                                            <Clock className="h-5 w-5 text-muted-foreground" />
                                            Status History
                                        </h2>
                                        <div className="space-y-4">
                                            {(
                                                maintenanceRequest.data
                                                    .status_logs || []
                                            ).map((log, index) => (
                                                <div
                                                    key={log.id}
                                                    className="flex gap-4 border-b pb-4 last:border-0 last:pb-0"
                                                >
                                                    <div className="flex flex-col items-center">
                                                        <div className={`h-3 w-3 rounded-full ${getStatusDotColor(log.to_status?.value)}`} />
                                                        {index <
                                                            (
                                                                maintenanceRequest
                                                                    .data
                                                                    .status_logs ||
                                                                []
                                                            ).length -
                                                                1 && (
                                                            <div className="mt-2 h-8 w-0.5 bg-border" />
                                                        )}
                                                    </div>
                                                    <div className="flex-1 pt-0.5">
                                                        <div className="mb-1 flex flex-wrap items-center gap-2">
                                                            {log.from_status ? (
                                                                <>
                                                                    <Badge
                                                                        variant="outline"
                                                                        className={`text-xs ${getStatusColor(log.from_status?.value)}`}
                                                                    >
                                                                        {log
                                                                            .from_status
                                                                            ?.label ||
                                                                            '—'}
                                                                    </Badge>
                                                                    <span className="text-xs text-muted-foreground">
                                                                        →
                                                                    </span>
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <Badge
                                                                        variant="secondary"
                                                                        className="text-xs"
                                                                    >
                                                                        Initial
                                                                    </Badge>
                                                                    <span className="text-xs text-muted-foreground">
                                                                        →
                                                                    </span>
                                                                </>
                                                            )}
                                                            {log.to_status && (
                                                                <Badge
                                                                    variant="outline"
                                                                    className={`text-xs ${getStatusColor(log.to_status?.value)}`}
                                                                >
                                                                    {log
                                                                        .to_status
                                                                        ?.label ||
                                                                        '—'}
                                                                </Badge>
                                                            )}
                                                        </div>
                                                        <p className="mb-1 text-xs text-muted-foreground">
                                                            by{' '}
                                                            {log.changed_by
                                                                ?.name || '—'}
                                                        </p>
                                                        <p className="mb-2 text-xs text-muted-foreground">
                                                            {format(
                                                                new Date(
                                                                    log.created_at,
                                                                ),
                                                                'MMM dd, yyyy • h:mm a',
                                                            )}
                                                        </p>
                                                        {log.notes && (
                                                            <p className="rounded bg-muted px-2 py-1 text-xs text-muted-foreground">
                                                                {log.notes}
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Right Sidebar */}
                        <div className="space-y-6">
                            {/* Resident Card */}
                            <div className="overflow-hidden rounded-lg border bg-card shadow-sm">
                                <div className="p-6">
                                    <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold">
                                        <User className="h-5 w-5 text-muted-foreground" />
                                        Resident
                                    </h2>
                                    <div className="mb-4 flex items-center gap-3">
                                        <Avatar className="h-10 w-10">
                                            <AvatarFallback className="font-semibold text-primary bg-primary/10">
                                                {maintenanceRequest.data
                                                    .resident?.name
                                                    ? maintenanceRequest.data.resident?.name
                                                          .split(' ')
                                                          .map((n) => n[0])
                                                          .join('')
                                                    : '-'}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <p className="font-semibold">
                                                {
                                                    maintenanceRequest.data
                                                        .resident?.name
                                                }
                                            </p>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        {maintenanceRequest.data.resident
                                            .email && (
                                            <a
                                                href={`mailto:${maintenanceRequest.data.resident.email}`}
                                                className="inline-flex items-center gap-2 text-sm font-medium break-all text-primary hover:text-primary/80"
                                            >
                                                {
                                                    maintenanceRequest.data
                                                        .resident.email
                                                }
                                            </a>
                                        )}
                                        {maintenanceRequest.data.resident
                                            .phone && (
                                            <a
                                                href={`tel:${maintenanceRequest.data.resident.phone}`}
                                                className="flex items-center gap-2 text-sm font-medium text-primary hover:text-primary/80"
                                            >
                                                <Phone className="h-4 w-4" />
                                                {
                                                    maintenanceRequest.data
                                                        .resident.phone
                                                }
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Assigned Technician Card */}
                            <div className="overflow-hidden rounded-lg border bg-card shadow-sm">
                                <div className="p-6">
                                    <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold">
                                        <Wrench className="h-5 w-5 text-muted-foreground" />
                                        Assigned Technician
                                    </h2>
                                    {maintenanceRequest.data
                                        .assigned_technician ? (
                                        <>
                                            <div className="mb-4 flex items-center gap-3">
                                                <Avatar className="h-10 w-10">
                                                    <AvatarFallback className="font-semibold text-primary bg-primary/10">
                                                        {maintenanceRequest.data.assigned_technician.name
                                                            .split(' ')
                                                            .map((n) => n[0])
                                                            .join('')}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <div>
                                                    <p className="font-semibold">
                                                        {
                                                            maintenanceRequest
                                                                .data
                                                                .assigned_technician
                                                                .name
                                                        }
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="space-y-2">
                                                {maintenanceRequest.data
                                                    .assigned_technician
                                                    .email && (
                                                    <a
                                                        href={`mailto:${maintenanceRequest.data.assigned_technician.email}`}
                                                        className="inline-flex items-center gap-2 text-sm font-medium break-all text-primary hover:text-primary/80"
                                                    >
                                                        {
                                                            maintenanceRequest
                                                                .data
                                                                .assigned_technician
                                                                .email
                                                        }
                                                    </a>
                                                )}
                                                {maintenanceRequest.data
                                                    .assigned_technician
                                                    .phone && (
                                                    <a
                                                        href={`tel:${maintenanceRequest.data.assigned_technician.phone}`}
                                                        className="flex items-center gap-2 text-sm font-medium text-primary hover:text-primary/80"
                                                    >
                                                        <Phone className="h-4 w-4" />
                                                        {
                                                            maintenanceRequest
                                                                .data
                                                                .assigned_technician
                                                                .phone
                                                        }
                                                    </a>
                                                )}
                                            </div>
                                        </>
                                    ) : (
                                        <div className="rounded-lg bg-muted px-3 py-2 text-sm text-muted-foreground">
                                            Technician not assigned
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Actual Cost Card */}
                            {maintenanceRequest.data.actual_cost && (
                                <div className="overflow-hidden rounded-lg border bg-card shadow-sm">
                                    <div className="p-6">
                                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold">
                                            <DollarSign className="h-5 w-5 text-muted-foreground" />
                                            Actual Cost
                                        </h2>
                                        <p className="text-3xl font-bold">
                                            $
                                            {parseFloat(
                                                maintenanceRequest.data
                                                    .actual_cost,
                                            ).toFixed(2)}
                                        </p>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {selectedImage && (
                <ImageModal
                    isOpen={!!selectedImage}
                    onClose={() => setSelectedImage(null)}
                    imageUrl={selectedImage?.url || ''}
                    imageName={selectedImage?.name || ''}
                />
            )}

            <AssignTechnicianDialog
                technicians={technicians}
                isReassigning={hasAssignedTechnician}
                data={assignForm.data}
                setData={assignForm.setData}
                errors={assignForm.errors}
                processing={assignForm.processing}
                onSubmit={submitAssignTechnician}
                open={assignDialogOpen}
                onOpenChange={setAssignDialogOpen}
            />

            <StatusUpdateDialog
                statuses={statuses}
                data={statusForm.data}
                setData={statusForm.setData}
                errors={statusForm.errors}
                processing={statusForm.processing}
                onSubmit={submitStatusUpdate}
                open={statusDialogOpen}
                onOpenChange={setStatusDialogOpen}
            />
        </>
    );
}
