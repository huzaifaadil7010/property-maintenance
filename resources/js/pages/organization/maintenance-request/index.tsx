import { Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { Eye } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { PageHeader } from '@/components/ui/page-header';
import { StatusBadge } from '@/components/ui/status-badge';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { show } from '@/wayfinder/App/Http/Controllers/Organization/MaintenanceRequestsController';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.MaintenanceRequest.Index;

type EnumOption = {
    label: string;
    value: string;
};

type RelatedUser = {
    id: number;
    name: string;
};

type MaintenanceRequestTableRow = {
    id: number;
    title: string;
    property: RelatedUser;
    unit: RelatedUser;
    resident: RelatedUser;
    assigned_technician: RelatedUser | null;
    category: EnumOption;
    priority: EnumOption;
    status: EnumOption;
    created_at: string | null;
};

export default function Index(props: GeneratedPageProps) {
    const { url, props: pageProps } = usePage();
    const maintenanceRequests = props.maintenanceRequests as unknown as {
        data: MaintenanceRequestTableRow[];
        meta: Record<
            'current_page' | 'last_page' | 'per_page' | 'total',
            number
        >;
    };
    const queryParameters = new URLSearchParams(url.split('?')[1] ?? '');
    const requestedPerPage = Number(queryParameters.get('perPage'));
    const [search, setSearch] = useState(queryParameters.get('search') ?? '');
    const isInitialRender = useRef(true);

    useEffect(() => {
        if (isInitialRender.current) {
            isInitialRender.current = false;

            return;
        }

        const reloadMaintenanceRequests = debounce(() => {
            router.reload({
                data: { search, page: 1 },
                only: ['maintenanceRequests'],
            });
        }, 300);

        reloadMaintenanceRequests();

        return () => reloadMaintenanceRequests.cancel();
    }, [search]);

    function onView(maintenanceRequest: MaintenanceRequestTableRow) {
        if (!pageProps.currentOrganization) {
            return;
        }

        router.visit(
            show({
                organization: pageProps.currentOrganization.uuid,
                maintenanceRequest: maintenanceRequest.id,
            }),
        );
    }

    const columns: ColumnDef<MaintenanceRequestTableRow>[] = [
        {
            accessorKey: 'title',
            header: 'Title',
        },
        {
            accessorKey: 'property.name',
            header: 'Property',
        },
        {
            accessorKey: 'unit.name',
            header: 'Unit',
        },
        {
            accessorKey: 'resident.name',
            header: 'Resident',
        },
        {
            accessorKey: 'assigned_technician.name',
            header: 'Technician',
            cell: ({ row }) => row.original.assigned_technician?.name ?? '—',
        },
        {
            accessorKey: 'category.label',
            header: 'Category',
            cell: ({ row }) => (
                <StatusBadge option={row.original.category} kind="neutral" />
            ),
        },
        {
            accessorKey: 'priority.label',
            header: 'Priority',
            cell: ({ row }) => (
                <StatusBadge option={row.original.priority} kind="priority" />
            ),
        },
        {
            accessorKey: 'status.label',
            header: 'Status',
            cell: ({ row }) => <StatusBadge option={row.original.status} />,
        },
        {
            accessorKey: 'created_at',
            header: 'Created At',
            cell: ({ row }) =>
                row.original.created_at
                    ? format(row.original.created_at, 'yyyy-MM-dd')
                    : '—',
        },
        {
            id: 'actions',
            header: () => <span className="sr-only">Actions</span>,
            cell: ({ row }) => (
                <div className="flex justify-end">
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="size-8"
                                aria-label="View maintenance request"
                                onClick={() => onView(row.original)}
                            >
                                <Eye />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>View request</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Maintenance Requests" />

            <div className="page-container">
                <PageHeader
                    eyebrow="Operations"
                    title="Maintenance requests"
                    description="Review, assign, and track maintenance work across your organization."
                />

                <div className="surface-card flex min-h-0 flex-1 flex-col overflow-hidden">
                    <div className="flex w-full justify-end border-b border-border/70 bg-muted/20 p-3 sm:p-4">
                        <Input
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Search maintenance requests..."
                            aria-label="Search maintenance requests"
                            className="w-full sm:max-w-sm"
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={maintenanceRequests.data}
                        renderMobileCard={(request) => (
                            <button
                                type="button"
                                onClick={() => onView(request)}
                                className="interactive-card grid w-full gap-3 rounded-2xl border bg-card p-4 text-left"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="font-semibold">
                                            {request.title}
                                        </p>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {request.property.name} · Unit{' '}
                                            {request.unit.name}
                                        </p>
                                    </div>
                                    <StatusBadge option={request.status} />
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    <StatusBadge
                                        option={request.priority}
                                        kind="priority"
                                    />
                                    <StatusBadge
                                        option={request.category}
                                        kind="neutral"
                                    />
                                </div>
                            </button>
                        )}
                        pagination={{
                            currentPage: maintenanceRequests.meta.current_page,
                            lastPage: maintenanceRequests.meta.last_page,
                            perPage:
                                requestedPerPage ||
                                maintenanceRequests.meta.per_page,
                            total: maintenanceRequests.meta.total,
                            onChange: (page, perPage) => {
                                router.reload({
                                    data: { page, perPage },
                                    only: ['maintenanceRequests'],
                                });
                            },
                        }}
                    />
                </div>
            </div>
        </>
    );
}
