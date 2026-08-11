import { Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { useEffect, useRef, useState } from 'react';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Resident.MaintenanceRequest.Index;

type EnumOption = {
    label: string;
    value: string;
};

type MyRequestTableRow = {
    id: number;
    title: string;
    category: EnumOption;
    priority: EnumOption;
    status: EnumOption;
    unit: { id: number; name: string };
    created_at: string | null;
};

export default function Index(props: GeneratedPageProps) {
    const { url } = usePage();
    const myRequests = props.myRequests as unknown as {
        data: MyRequestTableRow[];
        meta: Record<'current_page' | 'last_page' | 'per_page' | 'total', number>;
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

        const reloadMyRequests = debounce(() => {
            router.reload({
                data: { search, page: 1 },
                only: ['myRequests'],
            });
        }, 300);

        reloadMyRequests();

        return () => reloadMyRequests.cancel();
    }, [search]);

    const columns: ColumnDef<MyRequestTableRow>[] = [
        { accessorKey: 'title', header: 'Title' },
        {
            accessorKey: 'category.label',
            header: 'Category',
        },
        {
            accessorKey: 'priority.label',
            header: 'Priority',
        },
        {
            accessorKey: 'status.label',
            header: 'Status',
        },
        {
            accessorKey: 'unit.name',
            header: 'Unit',
        },
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
            <Head title="My Requests" />

            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="mx-auto flex w-full max-w-10xl flex-1 flex-col gap-6">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            My Requests
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            View and track your maintenance requests.
                        </p>
                    </div>

                    <div className="flex min-h-0 flex-1 flex-col rounded-xl bg-card">
                        <div className="flex w-full justify-end p-3 sm:p-4">
                            <Input
                                type="search"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Search my requests..."
                                aria-label="Search my requests"
                                className="w-full sm:max-w-sm"
                            />
                        </div>

                        <DataTable
                            columns={columns}
                            data={myRequests.data}
                            pagination={{
                                currentPage: myRequests.meta.current_page,
                                lastPage: myRequests.meta.last_page,
                                perPage:
                                    requestedPerPage || myRequests.meta.per_page,
                                total: myRequests.meta.total,
                                onChange: (page, perPage) => {
                                    router.reload({
                                        data: { search, page, perPage },
                                        only: ['myRequests'],
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
