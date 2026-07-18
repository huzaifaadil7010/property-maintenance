import { Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { useEffect, useRef, useState } from 'react';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.Unit.Index;

export default function Index(props: GeneratedPageProps) {
    const { url } = usePage();
    const units = props.units as unknown as {
        data: GeneratedPageProps['units'];
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

        const reloadUnits = debounce(() => {
            router.reload({
                data: { search, page: 1 },
                only: ['units'],
            });
        }, 300);

        reloadUnits();

        return () => reloadUnits.cancel();
    }, [search]);

    const columns: ColumnDef<GeneratedPageProps['units'][number]>[] = [
        {
            accessorKey: 'name',
            header: 'Unit',
        },
        {
            accessorKey: 'property.name',
            header: 'Property',
        },
        {
            accessorKey: 'floor',
            header: 'Floor',
            cell: ({ row }) => row.original.floor ?? '—',
        },
        {
            accessorKey: 'status.label',
            header: 'Status',
        },
        {
            accessorKey: 'created_at',
            header: 'Created At',
            cell: ({ row }) =>
                row.original.created_at
                    ? format(String(row.original.created_at), 'yyyy-MM-dd')
                    : '—',
        },
    ];

    return (
        <>
            <Head title="Units" />

            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="max-w-10xl mx-auto flex w-full flex-1 flex-col gap-6">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Units
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            View and manage the units in your organization.
                        </p>
                    </div>

                    <div className="flex min-h-0 flex-1 flex-col rounded-xl bg-card">
                        <div className="flex w-full justify-end p-3 sm:p-4">
                            <Input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Search units..."
                                aria-label="Search units"
                                className="w-full sm:max-w-sm"
                            />
                        </div>

                        <DataTable
                            columns={columns}
                            data={units.data}
                            pagination={{
                                currentPage: units.meta.current_page,
                                lastPage: units.meta.last_page,
                                perPage:
                                    requestedPerPage || units.meta.per_page,
                                total: units.meta.total,
                                onChange: (page, perPage) => {
                                    router.reload({
                                        data: { page, perPage },
                                        only: ['units'],
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
