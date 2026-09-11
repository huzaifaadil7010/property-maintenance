import { Head, Link, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { Eye } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { PageHeader } from '@/components/ui/page-header';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { StatusBadge } from '@/components/ui/status-badge';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { show } from '@/wayfinder/App/Http/Controllers/Technician/MaintenanceRequestsController';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Technician.MaintenanceRequest.Index;

type EnumOption = {
    label: string;
    value: string;
};

type MyJobTableRow = {
    id: number;
    title: string;
    property: { id: number; name: string };
    unit: { id: number; name: string };
    category: EnumOption;
    priority: EnumOption;
    status: EnumOption;
    created_at: string | null;
};

export default function Index(props: GeneratedPageProps) {
    const { url } = usePage();
    const myJobs = props.myJobs as unknown as {
        data: MyJobTableRow[];
        meta: Record<
            'current_page' | 'last_page' | 'per_page' | 'total',
            number
        >;
    };
    const jobStatuses = props.jobStatuses as unknown as EnumOption[];
    const queryParameters = new URLSearchParams(url.split('?')[1] ?? '');
    const requestedPerPage = Number(queryParameters.get('perPage'));
    const [filters, setFilters] = useState({
        search: queryParameters.get('search') ?? '',
        status: queryParameters.get('status') ?? '',
    });
    const previousFilters = useRef(filters);

    useEffect(() => {
        if (
            JSON.stringify(previousFilters.current) === JSON.stringify(filters)
        ) {
            return;
        }

        previousFilters.current = filters;

        const reloadMyJobs = debounce(() => {
            router.reload({
                data: { ...filters, page: 1 },
                only: ['myJobs'],
            });
        }, 300);

        reloadMyJobs();

        return () => reloadMyJobs.cancel();
    }, [filters]);

    const columns: ColumnDef<MyJobTableRow>[] = [
        { accessorKey: 'title', header: 'Title' },
        { accessorKey: 'property.name', header: 'Property' },
        { accessorKey: 'unit.name', header: 'Unit' },
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
                                aria-label="View job details"
                                asChild
                            >
                                <Link href={show(row.original.id).url}>
                                    <Eye />
                                </Link>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>View job details</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="My Jobs" />

            <div className="page-container">
                <PageHeader
                    eyebrow="Work queue"
                    title="My jobs"
                    description="View and prioritize the maintenance jobs assigned to you."
                />

                <div className="surface-card flex min-h-0 flex-1 flex-col overflow-hidden">
                    <div className="flex w-full flex-col gap-3 border-b border-border/70 bg-muted/20 p-3 sm:flex-row sm:justify-end sm:p-4">
                        <Select
                            value={filters.status || 'all'}
                            onValueChange={(status) =>
                                setFilters((current) => ({
                                    ...current,
                                    status: status === 'all' ? '' : status,
                                }))
                            }
                        >
                            <SelectTrigger
                                className="w-full sm:w-48"
                                aria-label="Filter jobs by status"
                            >
                                <SelectValue placeholder="All jobs" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All jobs</SelectItem>
                                {jobStatuses.map((status) => (
                                    <SelectItem
                                        key={status.value}
                                        value={status.value}
                                    >
                                        {status.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Input
                            type="search"
                            value={filters.search}
                            onChange={(event) =>
                                setFilters((current) => ({
                                    ...current,
                                    search: event.target.value,
                                }))
                            }
                            placeholder="Search my jobs..."
                            aria-label="Search my jobs"
                            className="w-full sm:max-w-sm"
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={myJobs.data}
                        renderMobileCard={(job) => (
                            <Link
                                href={show(job.id).url}
                                className="interactive-card grid gap-3 rounded-2xl border bg-card p-4"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="font-semibold">
                                            {job.title}
                                        </p>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {job.property.name} · Unit{' '}
                                            {job.unit.name}
                                        </p>
                                    </div>
                                    <StatusBadge option={job.status} />
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    <StatusBadge
                                        option={job.priority}
                                        kind="priority"
                                    />
                                    <StatusBadge
                                        option={job.category}
                                        kind="neutral"
                                    />
                                </div>
                            </Link>
                        )}
                        pagination={{
                            currentPage: myJobs.meta.current_page,
                            lastPage: myJobs.meta.last_page,
                            perPage: requestedPerPage || myJobs.meta.per_page,
                            total: myJobs.meta.total,
                            onChange: (page, perPage) => {
                                router.reload({
                                    data: { ...filters, page, perPage },
                                    only: ['myJobs'],
                                });
                            },
                        }}
                    />
                </div>
            </div>
        </>
    );
}
