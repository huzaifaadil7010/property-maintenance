import { Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import { DataTable } from '@/components/ui/data-table';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.Property.Index;

export default function Index(props: GeneratedPageProps) {
    const { url } = usePage();
    const properties = props.properties as unknown as {
        data: GeneratedPageProps['properties'];
        meta: Record<
            'current_page' | 'last_page' | 'per_page' | 'total',
            number
        >;
    };
    const queryParameters = new URLSearchParams(url.split('?')[1] ?? '');
    const requestedPerPage = Number(queryParameters.get('perPage'));

    const columns: ColumnDef<GeneratedPageProps['properties'][number]>[] = [
        {
            accessorKey: 'name',
            header: 'Name',
        },
        {
            accessorKey: 'type.label',
            header: 'Type',
        },
        {
            accessorKey: 'city',
            header: 'City',
        },
        {
            accessorKey: 'address',
            header: 'Address',
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
            <Head title="Properties" />

            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="max-w-10xl mx-auto flex w-full flex-1 flex-col gap-6">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Properties
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            View and manage the properties in your organization.
                        </p>
                    </div>

                    <div className="flex min-h-0 flex-1 flex-col rounded-xl bg-card">
                        <DataTable
                            columns={columns}
                            data={properties.data}
                            pagination={{
                                currentPage: properties.meta.current_page,
                                lastPage: properties.meta.last_page,
                                perPage:
                                    requestedPerPage ||
                                    properties.meta.per_page,
                                total: properties.meta.total,
                                onChange: (page, perPage) => {
                                    router.reload({
                                        data: { page, perPage },
                                        only: ['properties'],
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
