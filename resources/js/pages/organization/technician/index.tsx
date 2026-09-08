import { Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import debounce from 'lodash.debounce';
import { useEffect, useRef, useState } from 'react';
import CreateTechnicianDialogue from '@/components/organization/technician/create-technician-dialogue';
import type { TechnicianSpecialtyOption } from '@/components/organization/technician/create-technician-dialogue';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
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
            cell: ({ row }) =>
                row.original.is_available ? 'Available' : 'Unavailable',
        },
    ];

    return (
        <>
            <Head title="Technicians" />

            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="max-w-10xl mx-auto flex w-full flex-1 flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div className="space-y-1">
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Technicians
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                View and manage the technicians in your
                                organization.
                            </p>
                        </div>

                        <CreateTechnicianDialogue
                            specialties={technicianSpecialties}
                            only={['technicians']}
                        />
                    </div>

                    <div className="flex min-h-0 flex-1 flex-col rounded-xl bg-card">
                        <div className="flex w-full justify-end p-3 sm:p-4">
                            <Input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(event.target.value)
                                }
                                placeholder="Search technicians..."
                                aria-label="Search technicians"
                                className="w-full sm:max-w-sm"
                            />
                        </div>

                        <DataTable
                            columns={columns}
                            data={technicians.data}
                            pagination={{
                                currentPage: technicians.meta.current_page,
                                lastPage: technicians.meta.last_page,
                                perPage:
                                    requestedPerPage ||
                                    technicians.meta.per_page,
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
            </div>
        </>
    );
}
