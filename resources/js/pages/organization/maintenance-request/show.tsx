import { Head } from '@inertiajs/react';
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Clock,
    DollarSign,
    Image as ImageIcon,
    MapPin,
    MessageSquare,
    Paperclip,
    Phone,
    User,
    Wrench,
} from 'lucide-react';
import { format } from 'date-fns';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { ImageModal } from '@/components/organization/maintenance-request/image-modal';
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

const getPriorityColor = (priority: string | null | undefined): string => {
    if (!priority) {
        return 'bg-slate-500/15 text-slate-700 border-slate-200 dark:border-slate-800 dark:text-slate-300';
    }

    const colors: Record<string, string> = {
        high: 'bg-red-500/15 text-red-700 border-red-200 dark:border-red-900 dark:text-red-300',
        medium: 'bg-amber-500/15 text-amber-700 border-amber-200 dark:border-amber-900 dark:text-amber-300',
        low: 'bg-blue-500/15 text-blue-700 border-blue-200 dark:border-blue-900 dark:text-blue-300',
    };
    return (
        colors[priority.toLowerCase()] ||
        'bg-slate-500/15 text-slate-700 border-slate-200 dark:border-slate-800 dark:text-slate-300'
    );
};

const getStatusColor = (status: string | null | undefined): string => {
    if (!status) {
        return 'bg-slate-500/15 text-slate-700 border-slate-200 dark:border-slate-800 dark:text-slate-300';
    }
    const colors: Record<string, string> = {
        pending:
            'bg-slate-500/15 text-slate-700 border-slate-200 dark:border-slate-800 dark:text-slate-300',
        open: 'bg-slate-500/15 text-slate-700 border-slate-200 dark:border-slate-800 dark:text-slate-300',
        in_progress:
            'bg-blue-500/15 text-blue-700 border-blue-200 dark:border-blue-900 dark:text-blue-300',
        completed:
            'bg-green-500/15 text-green-700 border-green-200 dark:border-green-900 dark:text-green-300',
        cancelled:
            'bg-red-500/15 text-red-700 border-red-200 dark:border-red-900 dark:text-red-300',
    };
    return (
        colors[status.toLowerCase()] ||
        'bg-slate-500/15 text-slate-700 border-slate-200 dark:border-slate-800 dark:text-slate-300'
    );
};

const getStatusIcon = (status: string | null | undefined) => {
    const icons: Record<string, JSX.Element> = {
        pending: <AlertCircle className="h-4 w-4" />,
        open: <AlertCircle className="h-4 w-4" />,
        in_progress: <Clock className="h-4 w-4" />,
        completed: <CheckCircle2 className="h-4 w-4" />,
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

export default function Show({ maintenanceRequest }: GeneratedPageProps) {
    const [selectedImage, setSelectedImage] = useState<{
        url: string;
        name: string;
    } | null>(null);

    const imageAttachments = (maintenanceRequest.data.attachments || []).filter(
        (attachment) => isImageFile(attachment.mime_type),
    );

    return (
        <>
            <Head title={maintenanceRequest.data.title} />

            <div className="flex min-h-0 flex-1 flex-col bg-slate-50 p-4 md:p-6 lg:p-8 dark:bg-slate-950">
                <div className="mx-auto w-full max-w-6xl flex-1">
                    {/* Header Section */}
                    <div className="mb-8">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div className="space-y-2">
                                <h1 className="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">
                                    {maintenanceRequest.data.title}
                                </h1>
                                <p className="text-sm text-slate-600 dark:text-slate-400">
                                    Request ID:{' '}
                                    <span className="font-semibold text-slate-900 dark:text-slate-200">
                                        #{maintenanceRequest.data.id}
                                    </span>
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <Badge
                                    variant="outline"
                                    className={`flex items-center gap-2 border px-4 py-2 text-sm font-semibold ${getStatusColor(maintenanceRequest.data.status?.value)}`}
                                >
                                    {getStatusIcon(
                                        maintenanceRequest.data.status?.value,
                                    )}
                                    {maintenanceRequest.data.status?.label ||
                                        '—'}
                                </Badge>
                                <Badge
                                    variant="outline"
                                    className={`border px-4 py-2 text-sm font-semibold ${getPriorityColor(maintenanceRequest.data.priority?.value)}`}
                                >
                                    {maintenanceRequest.data.priority?.label ||
                                        '—'}
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-3">
                        <div className="space-y-6 lg:col-span-2">
                            {/* Description */}
                            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div className="p-6">
                                    <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                        <MessageSquare className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        Description
                                    </h2>
                                    <p className="text-base leading-relaxed whitespace-pre-wrap text-slate-700 dark:text-slate-300">
                                        {maintenanceRequest.data.description}
                                    </p>
                                </div>
                            </div>

                            {/* Location & Category Grid */}
                            <div className="grid gap-4 sm:grid-cols-2">
                                {/* Location Card */}
                                <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                    <div className="p-6">
                                        <div className="mb-4 flex items-center gap-2">
                                            <MapPin className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            <h3 className="text-sm font-semibold text-slate-900 dark:text-white">
                                                Location
                                            </h3>
                                        </div>
                                        <div className="space-y-3">
                                            <div>
                                                <p className="text-xs font-medium text-slate-600 dark:text-slate-400">
                                                    Property
                                                </p>
                                                <p className="text-sm font-semibold text-slate-900 dark:text-white">
                                                    {
                                                        maintenanceRequest
                                                            .property?.name
                                                    }
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-xs font-medium text-slate-600 dark:text-slate-400">
                                                    Unit
                                                </p>
                                                <p className="text-sm font-semibold text-slate-900 dark:text-white">
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
                                <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                    <div className="p-6">
                                        <div className="mb-4 flex items-center gap-2">
                                            <Wrench className="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                            <h3 className="text-sm font-semibold text-slate-900 dark:text-white">
                                                Category
                                            </h3>
                                        </div>
                                        <p className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            {maintenanceRequest.data.category
                                                ?.label || '—'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Timeline */}
                            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div className="p-6">
                                    <h2 className="mb-6 flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                        <Calendar className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                        Timeline
                                    </h2>
                                    <div className="space-y-6">
                                        {/* Created */}
                                        <div className="flex gap-4">
                                            <div className="flex flex-col items-center pt-1">
                                                <div className="h-3 w-3 rounded-full bg-blue-500" />
                                                <div className="mt-3 h-12 w-0.5 bg-slate-200 dark:bg-slate-700" />
                                            </div>
                                            <div className="pb-4">
                                                <p className="text-xs font-medium text-slate-600 dark:text-slate-400">
                                                    Created
                                                </p>
                                                <p className="mt-2 text-sm font-semibold text-slate-900 dark:text-white">
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
                                                    <div className="h-3 w-3 rounded-full bg-emerald-500" />
                                                    <div className="mt-3 h-12 w-0.5 bg-slate-200 dark:bg-slate-700" />
                                                </div>
                                                <div className="pb-4">
                                                    <p className="text-xs font-medium text-slate-600 dark:text-slate-400">
                                                        Completed
                                                    </p>
                                                    <p className="mt-2 text-sm font-semibold text-slate-900 dark:text-white">
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
                                                <div className="h-3 w-3 rounded-full bg-slate-400" />
                                            </div>
                                            <div>
                                                <p className="text-xs font-medium text-slate-600 dark:text-slate-400">
                                                    Last Updated
                                                </p>
                                                <p className="mt-2 text-sm font-semibold text-slate-900 dark:text-white">
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
                                <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                    <div className="p-6">
                                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                            <CheckCircle2 className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                            Completion Notes
                                        </h2>
                                        <p className="text-base leading-relaxed whitespace-pre-wrap text-slate-700 dark:text-slate-300">
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
                                <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                    <div className="p-6">
                                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                            <ImageIcon className="h-5 w-5 text-purple-600 dark:text-purple-400" />
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
                                                        className="group relative overflow-hidden rounded-lg border border-slate-200 bg-transparent p-0 text-left transition-all hover:bg-transparent hover:shadow-md dark:border-slate-700 dark:hover:border-slate-600"
                                                    >
                                                        <div className="aspect-square overflow-hidden bg-slate-100 dark:bg-slate-800">
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
                                                        <div className="bg-white p-3 dark:bg-slate-800">
                                                            <p className="truncate text-xs font-medium text-slate-900 dark:text-white">
                                                                {
                                                                    attachment.original_name
                                                                }
                                                            </p>
                                                            <p className="truncate text-xs text-slate-600 dark:text-slate-400">
                                                                {formatBytes(
                                                                    attachment.size,
                                                                )}
                                                            </p>
                                                            <p className="mt-1 truncate text-xs text-slate-500 dark:text-slate-500">
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
                                <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                    <div className="p-6">
                                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                            <Clock className="h-5 w-5 text-slate-600 dark:text-slate-400" />
                                            Status History
                                        </h2>
                                        <div className="space-y-4">
                                            {(
                                                maintenanceRequest.data
                                                    .status_logs || []
                                            ).map((log, index) => (
                                                <div
                                                    key={log.id}
                                                    className="flex gap-4 border-b border-slate-200 pb-4 last:border-0 last:pb-0 dark:border-slate-700"
                                                >
                                                    <div className="flex flex-col items-center">
                                                        <div className="h-3 w-3 rounded-full bg-slate-400" />
                                                        {index <
                                                            (
                                                                maintenanceRequest
                                                                    .data
                                                                    .status_logs ||
                                                                []
                                                            ).length -
                                                                1 && (
                                                            <div className="mt-2 h-8 w-0.5 bg-slate-200 dark:bg-slate-700" />
                                                        )}
                                                    </div>
                                                    <div className="flex-1 pt-0.5">
                                                        <div className="mb-1 flex flex-wrap items-center gap-2">
                                                            {log.from_status ? (
                                                                <>
                                                                    <Badge
                                                                        variant="outline"
                                                                        className={`border text-xs ${getStatusColor(log.from_status?.value)}`}
                                                                    >
                                                                        {log
                                                                            .from_status
                                                                            ?.label ||
                                                                            '—'}
                                                                    </Badge>
                                                                    <span className="text-xs text-slate-500 dark:text-slate-400">
                                                                        →
                                                                    </span>
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <Badge
                                                                        variant="outline"
                                                                        className="border bg-slate-100 text-xs text-slate-600 dark:bg-slate-700 dark:text-slate-300"
                                                                    >
                                                                        Initial
                                                                    </Badge>
                                                                    <span className="text-xs text-slate-500 dark:text-slate-400">
                                                                        →
                                                                    </span>
                                                                </>
                                                            )}
                                                            {log.to_status && (
                                                                <Badge
                                                                    variant="outline"
                                                                    className={`border text-xs ${getStatusColor(log.to_status?.value)}`}
                                                                >
                                                                    {log
                                                                        .to_status
                                                                        ?.label ||
                                                                        '—'}
                                                                </Badge>
                                                            )}
                                                        </div>
                                                        <p className="mb-1 text-xs text-slate-600 dark:text-slate-400">
                                                            by{' '}
                                                            {log.changed_by
                                                                ?.name || '—'}
                                                        </p>
                                                        <p className="mb-2 text-xs text-slate-500 dark:text-slate-500">
                                                            {format(
                                                                new Date(
                                                                    log.created_at,
                                                                ),
                                                                'MMM dd, yyyy • h:mm a',
                                                            )}
                                                        </p>
                                                        {log.notes && (
                                                            <p className="rounded bg-slate-100 px-2 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">
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
                            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div className="p-6">
                                    <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                        <User className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        Resident
                                    </h2>
                                    <div className="mb-4 flex items-center gap-3">
                                        <Avatar className="h-10 w-10 bg-gradient-to-br from-blue-400 to-blue-600">
                                            <AvatarFallback className="font-semibold text-slate-900 dark:text-white">
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
                                            <p className="font-semibold text-slate-900 dark:text-white">
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
                                                className="inline-flex items-center gap-2 text-sm font-medium break-all text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
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
                                                className="flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
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
                            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div className="p-6">
                                    <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                        <Wrench className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                        Assigned Technician
                                    </h2>
                                    {maintenanceRequest.data
                                        .assigned_technician ? (
                                        <>
                                            <div className="mb-4 flex items-center gap-3">
                                                <Avatar className="h-10 w-10 bg-gradient-to-br from-green-400 to-green-600">
                                                    <AvatarFallback className="font-semibold text-slate-900 dark:text-white">
                                                        {maintenanceRequest.data.assigned_technician.name
                                                            .split(' ')
                                                            .map((n) => n[0])
                                                            .join('')}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <div>
                                                    <p className="font-semibold text-slate-900 dark:text-white">
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
                                                        className="inline-flex items-center gap-2 text-sm font-medium break-all text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
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
                                                        className="flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
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
                                        <div className="rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                            Technician not assigned
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Actual Cost Card */}
                            {maintenanceRequest.data.actual_cost && (
                                <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                    <div className="p-6">
                                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white">
                                            <DollarSign className="h-5 w-5 text-amber-600 dark:text-amber-400" />
                                            Actual Cost
                                        </h2>
                                        <p className="text-3xl font-bold text-slate-900 dark:text-white">
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
        </>
    );
}
