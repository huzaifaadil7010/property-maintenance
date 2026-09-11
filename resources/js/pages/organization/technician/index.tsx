import { Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import debounce from 'lodash.debounce';
import { useEffect, useRef, useState } from 'react';
import CreateTechnicianDialogue from '@/components/organization/technician/create-technician-dialogue';
import type { TechnicianSpecialtyOption } from '@/components/organization/technician/create-technician-dialogue';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { PageHeader } from '@/components/ui/page-header';
import { StatusBadge } from '@/components/ui/status-badge';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.Technician.Index;

export default function Index(props: GeneratedPageProps) {
    const { url } = usePage();
    const technicians = props.technicians as unknown as {
        data: GeneratedPageProps['technicians'];
        meta: Record<
            'current_page' | 'last_page' | 'per_page' | 'total',
            number
        >;
    };
    const technicianSpecialties =
        props.technicianSpecialties as unknown as TechnicianSpecialtyOption[];
    const queryParameters = new URLSearchParams(url.split('?')[1] ?? '');
    const requestedPerPage = Number(queryParameters.get('perPage'));
    const [search, setSearch] = useState(queryParameters.get('search') ?? '');
    const isInitialRender = useRef(true);

    useEffect(() => {
        if (isInitialRender.current) {
            isInitialRender.current = false;

            return;
        }

        const reloadTechnicians = debounce(() => {
            router.reload({
                data: { search, page: 1 },
                only: ['technicians'],
            });
        }, 300);

        reloadTechnicians();

        return () => reloadTechnicians.cancel();
    }, [search]);

    const columns: ColumnDef<GeneratedPageProps['technicians'][number]>[] = [
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
            accessorKey: 'specialty.label',
            header: 'Specialty',
            cell: ({ row }) => row.original.specialty?.label ?? '—',
        },
        {
            accessorKey: 'assigned_requests_count',
            header: 'Assigned Requests',
        },
        {
            accessorKey: 'is_available',
            header: 'Availability',
            cell: ({ row }) => (
                <StatusBadge
                    option={{
                        label: row.original.is_available
                            ? 'Available'
                            : 'Unavailable',
                        value: row.original.is_available
                            ? 'available'
                            : 'unavailable',
                    }}
                />
            ),
        },
    ];

    return (
        <>
            <Head title="Technicians" />

            <div className="page-container">
                <PageHeader
                    eyebrow="People"
                    title="Technicians"
                    description="View and manage the technicians in your organization."
                    actions={
                        <CreateTechnicianDialogue
                            specialties={technicianSpecialties}
                            only={['technicians']}
                        />
                    }
                />

                <div className="surface-card flex min-h-0 flex-1 flex-col overflow-hidden">
                    <div className="flex w-full justify-end border-b border-border/70 bg-muted/20 p-3 sm:p-4">
                        <Input
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Search technicians..."
                            aria-label="Search technicians"
                            className="w-full sm:max-w-sm"
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={technicians.data}
                        renderMobileCard={(technician) => (
                            <div className="interactive-card grid gap-3 rounded-2xl border bg-card p-4">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="font-semibold">
                                            {String(technician.name)}
                                        </p>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {String(technician.email)}
                                        </p>
                                    </div>
                                    <StatusBadge
                                        option={{
                                            label: technician.is_available
                                                ? 'Available'
                                                : 'Unavailable',
                                            value: technician.is_available
                                                ? 'available'
                                                : 'unavailable',
                                        }}
                                    />
                                </div>
                                <div className="flex items-center justify-between gap-3 border-t border-border/70 pt-3 text-sm">
                                    <p className="text-muted-foreground">
                                        {String(
                                            technician.specialty?.label ??
                                                'General maintenance',
                                        )}
                                    </p>
                                    <p className="font-medium">
                                        {String(
                                            technician.assigned_requests_count,
                                        )}{' '}
                                        assigned
                                    </p>
                                </div>
                            </div>
                        )}
                        pagination={{
                            currentPage: technicians.meta.current_page,
                            lastPage: technicians.meta.last_page,
                            perPage:
                                requestedPerPage || technicians.meta.per_page,
                            total: technicians.meta.total,
                            onChange: (page, perPage) => {
                                router.reload({
                                    data: { page, perPage },
                                    only: ['technicians'],
                                });
                            },
                        }}
                    />
                </div>
            </div>
        </>
    );
}
