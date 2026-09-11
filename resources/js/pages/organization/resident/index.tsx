import { Deferred, Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { useEffect, useRef, useState } from 'react';
import CreateResidentDialogue from '@/components/organization/resident/create-resident-dialogue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { PageHeader } from '@/components/ui/page-header';
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

            <div className="page-container">
                <PageHeader
                    eyebrow="People"
                    title="Residents"
                    description="View and manage the residents in your organization."
                    actions={
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
                    }
                />

                <div className="surface-card flex min-h-0 flex-1 flex-col overflow-hidden">
                    <div className="flex w-full justify-end border-b border-border/70 bg-muted/20 p-3 sm:p-4">
                        <Input
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Search residents..."
                            aria-label="Search residents"
                            className="w-full sm:max-w-sm"
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={residents.data}
                        renderMobileCard={(resident) => (
                            <div className="interactive-card grid gap-3 rounded-2xl border bg-card p-4">
                                <div>
                                    <p className="font-semibold">
                                        {String(resident.name)}
                                    </p>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {String(resident.email)}
                                    </p>
                                </div>
                                <div className="grid grid-cols-2 gap-3 border-t border-border/70 pt-3 text-sm">
                                    <div>
                                        <p className="text-xs text-muted-foreground">
                                            Property
                                        </p>
                                        <p className="mt-1 font-medium">
                                            {String(
                                                resident.property_name ?? '—',
                                            )}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-muted-foreground">
                                            Unit
                                        </p>
                                        <p className="mt-1 font-medium">
                                            {String(resident.unit_name ?? '—')}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        )}
                        pagination={{
                            currentPage: residents.meta.current_page,
                            lastPage: residents.meta.last_page,
                            perPage:
                                requestedPerPage || residents.meta.per_page,
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
        </>
    );
}
