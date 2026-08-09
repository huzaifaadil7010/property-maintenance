import { Deferred, Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { useEffect, useRef, useState } from 'react';
import CreateResidentDialogue from '@/components/organization/resident/create-resident-dialogue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.Resident.Index;

export default function Index(props: GeneratedPageProps) {
    const { url } = usePage();
    const residents = props.residents as unknown as {
        data: GeneratedPageProps['residents'];
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

        const reloadResidents = debounce(() => {
            router.reload({
                data: { search, page: 1 },
                only: ['residents'],
            });
        }, 300);

        reloadResidents();

        return () => reloadResidents.cancel();
    }, [search]);

    const columns: ColumnDef<GeneratedPageProps['residents'][number]>[] = [
        {
            accessorKey: 'name',
            header: 'Name',
        },
        {
            accessorKey: 'email',
            header: 'Email',
        },
        {
            accessorKey: 'phone',
            header: 'Phone',
            cell: ({ row }) => row.original.phone ?? '—',
        },
        {
            accessorKey: 'property_name',
            header: 'Property',
            cell: ({ row }) => row.original.property_name ?? '—',
        },
        {
            accessorKey: 'unit_name',
            header: 'Unit',
            cell: ({ row }) => row.original.unit_name ?? '—',
        },
        {
            accessorKey: 'move_in_date',
            header: 'Move-in Date',
            cell: ({ row }) =>
                row.original.move_in_date
                    ? format(String(row.original.move_in_date), 'yyyy-MM-dd')
                    : '—',
        },
    ];

    return (
        <>
            <Head title="Residents" />

            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="max-w-10xl mx-auto flex w-full flex-1 flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div className="space-y-1">
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Residents
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                View and manage the residents in your organization.
                            </p>
                        </div>

                        <Deferred
                            data="residentCreateOptions"
                            fallback={
                                <Button className="w-full sm:w-auto" disabled>
                                    <Spinner />
                                    Create resident
                                </Button>
                            }
                        >
                            <CreateResidentDialogue
                                only={['residents', 'residentCreateOptions']}
                            />
                        </Deferred>
                    </div>

                    <div className="flex min-h-0 flex-1 flex-col rounded-xl bg-card">
                        <div className="flex w-full justify-end p-3 sm:p-4">
                            <Input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Search residents..."
                                aria-label="Search residents"
                                className="w-full sm:max-w-sm"
                            />
                        </div>

                        <DataTable
                            columns={columns}
                            data={residents.data}
                            pagination={{
                                currentPage: residents.meta.current_page,
                                lastPage: residents.meta.last_page,
                                perPage:
                                    requestedPerPage ||
                                    residents.meta.per_page,
                                total: residents.meta.total,
                                onChange: (page, perPage) => {
                                    router.reload({
                                        data: { page, perPage },
                                        only: ['residents'],
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
