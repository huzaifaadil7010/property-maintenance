import { Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { useEffect, useRef, useState } from 'react';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
        { accessorKey: 'category.label', header: 'Category' },
        { accessorKey: 'priority.label', header: 'Priority' },
        { accessorKey: 'status.label', header: 'Status' },
        {
            accessorKey: 'created_at',
            header: 'Created At',
            cell: ({ row }) =>
                row.original.created_at
                    ? format(row.original.created_at, 'yyyy-MM-dd')
                    : '—',
        },
    ];

    return (
        <>
            <Head title="My Jobs" />

            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="max-w-10xl mx-auto flex w-full flex-1 flex-col gap-6">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            My Jobs
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            View the maintenance jobs assigned to you.
                        </p>
                    </div>

                    <div className="flex min-h-0 flex-1 flex-col rounded-xl bg-card">
                        <div className="flex w-full flex-col gap-3 p-3 sm:flex-row sm:justify-end sm:p-4">
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
                                    <SelectItem value="all">
                                        All jobs
                                    </SelectItem>
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
                            pagination={{
                                currentPage: myJobs.meta.current_page,
                                lastPage: myJobs.meta.last_page,
                                perPage:
                                    requestedPerPage || myJobs.meta.per_page,
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
            </div>
        </>
    );
}
