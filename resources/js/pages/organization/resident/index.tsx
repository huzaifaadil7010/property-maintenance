import { Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { Pencil } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import ResidentDialogue from '@/components/organization/resident/resident-dialogue';
import type { EditableResident } from '@/components/organization/resident/resident-dialogue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { PageHeader } from '@/components/ui/page-header';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.Resident.Index;

export default function Index(props: GeneratedPageProps) {
    const { url } = usePage();
    const paginatedResidents = props.residents as unknown as {
        data: EditableResident[];
        meta: Record<
            'current_page' | 'last_page' | 'per_page' | 'total',
            number
        >;
    };
    const queryParameters = new URLSearchParams(url.split('?')[1] ?? '');
    const requestedPerPage = Number(queryParameters.get('perPage'));
    const [residentSearch, setResidentSearch] = useState(
        queryParameters.get('search') ?? '',
    );
    const [residentBeingEdited, setResidentBeingEdited] =
        useState<EditableResident | null>(null);
    const shouldSkipInitialSearchReload = useRef(true);
    const displayedPerPage =
        requestedPerPage || paginatedResidents.meta.per_page;

    useEffect(() => {
        if (shouldSkipInitialSearchReload.current) {
            shouldSkipInitialSearchReload.current = false;

            return;
        }

        const reloadResidentsAfterSearch = debounce(() => {
            router.reload({
                data: { search: residentSearch, page: 1 },
                only: ['residents'],
            });
        }, 300);

        reloadResidentsAfterSearch();

        return () => reloadResidentsAfterSearch.cancel();
    }, [residentSearch]);

    const residentColumns: ColumnDef<EditableResident>[] = [
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
            cell: ({ row: residentRow }) => residentRow.original.phone ?? '—',
        },
        {
            accessorKey: 'property_name',
            header: 'Property',
            cell: ({ row: residentRow }) =>
                residentRow.original.property_name ?? '—',
        },
        {
            accessorKey: 'unit_name',
            header: 'Unit',
            cell: ({ row: residentRow }) =>
                residentRow.original.unit_name ?? '—',
        },
        {
            accessorKey: 'move_in_date',
            header: 'Move-in Date',
            cell: ({ row: residentRow }) =>
                residentRow.original.move_in_date
                    ? format(
                          String(residentRow.original.move_in_date),
                          'yyyy-MM-dd',
                      )
                    : '—',
        },
        {
            id: 'actions',
            header: () => <span className="sr-only">Actions</span>,
            cell: ({ row: residentRow }) => (
                <div className="flex justify-end">
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="size-8"
                                aria-label={`Edit ${residentRow.original.name}`}
                                onClick={() =>
                                    setResidentBeingEdited(residentRow.original)
                                }
                            >
                                <Pencil />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>Edit resident</TooltipContent>
                    </Tooltip>
                </div>
            ),
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
                        <ResidentDialogue
                            propsToRefresh={[
                                'residents',
                                'residentCreateOptions',
                            ]}
                        />
                    }
                />

                <div className="surface-card flex min-h-0 flex-col overflow-hidden">
                    <div className="flex w-full justify-end border-b border-border/70 bg-muted/20 p-3 sm:p-4">
                        <Input
                            type="search"
                            value={residentSearch}
                            onChange={(searchChangeEvent) =>
                                setResidentSearch(
                                    searchChangeEvent.target.value,
                                )
                            }
                            placeholder="Search residents..."
                            aria-label="Search residents"
                            className="w-full sm:max-w-sm"
                        />
                    </div>

                    <DataTable
                        columns={residentColumns}
                        data={paginatedResidents.data}
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
                                <div className="flex justify-end border-t border-border/70 pt-2">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        className="size-8"
                                        aria-label={`Edit ${resident.name}`}
                                        onClick={() =>
                                            setResidentBeingEdited(resident)
                                        }
                                    >
                                        <Pencil />
                                    </Button>
                                </div>
                            </div>
                        )}
                        pagination={{
                            currentPage: paginatedResidents.meta.current_page,
                            lastPage: paginatedResidents.meta.last_page,
                            perPage: displayedPerPage,
                            total: paginatedResidents.meta.total,
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

            {residentBeingEdited && (
                <ResidentDialogue
                    key={residentBeingEdited.id}
                    residentToEdit={residentBeingEdited}
                    propsToRefresh={['residents', 'residentCreateOptions']}
                    isOpen
                    onOpenChange={(shouldOpenEditor) => {
                        if (!shouldOpenEditor) {
                            setResidentBeingEdited(null);
                        }
                    }}
                />
            )}
        </>
    );
}
