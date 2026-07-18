import { Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { useEffect, useRef, useState } from 'react';
import CreatePropertyDialogue from '@/components/organization/common/create-property-dialogue';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
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
    const [search, setSearch] = useState(queryParameters.get('search') ?? '');
    const isInitialRender = useRef(true);

    useEffect(() => {
        if (isInitialRender.current) {
            isInitialRender.current = false;

            return;
        }

        const reloadProperties = debounce(() => {
            router.reload({
                data: { search, page: 1 },
                only: ['properties'],
            });
        }, 300);

        reloadProperties();

        return () => reloadProperties.cancel();
    }, [search]);

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
            accessorKey: 'units_count',
            header: 'Total Units',
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
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div className="space-y-1">
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Properties
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                View and manage the properties in your
                                organization.
                            </p>
                        </div>

                        <CreatePropertyDialogue />
                    </div>

                    <div className="flex min-h-0 flex-1 flex-col rounded-xl bg-card">
                        <div className="flex w-full justify-end p-3 sm:p-4">
                            <Input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Search properties..."
                                aria-label="Search properties"
                                className="w-full sm:max-w-sm"
                            />
                        </div>

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
