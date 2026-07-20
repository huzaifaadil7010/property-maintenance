import { Deferred, Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { Pencil } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import CreateUnitDialogue from '@/components/organization/common/create-unit-dialogue';
import type {
    PropertyOption,
    UnitStatusOption,
} from '@/components/organization/common/create-unit-dialogue';
import EditUnitDialogue from '@/components/organization/common/edit-unit-dialogue';
import type { EditableUnit } from '@/components/organization/common/edit-unit-dialogue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.Unit.Index;

type UnitTableRow = EditableUnit & {
    created_at: unknown;
};

export default function Index(props: GeneratedPageProps) {
    const { url } = usePage();
    const units = props.units as unknown as {
        data: UnitTableRow[];
        meta: Record<
            'current_page' | 'last_page' | 'per_page' | 'total',
            number
        >;
    };
    const properties = props.properties as PropertyOption[] | undefined;
    const unitStatuses = props.unitStatuses as unknown as UnitStatusOption[];
    const queryParameters = new URLSearchParams(url.split('?')[1] ?? '');
    const requestedPerPage = Number(queryParameters.get('perPage'));
    const [search, setSearch] = useState(queryParameters.get('search') ?? '');
    const [unitBeingEdited, setUnitBeingEdited] = useState<UnitTableRow | null>(
        null,
    );
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

    function onEdit(unit: UnitTableRow) {
        setUnitBeingEdited(unit);
    }

    const columns: ColumnDef<UnitTableRow>[] = [
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
        {
            id: 'actions',
            header: () => <span className="sr-only">Actions</span>,
            cell: ({ row }) => (
                <div className="flex justify-end">
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="size-8"
                                aria-label="Edit unit"
                                onClick={() => onEdit(row.original)}
                            >
                                <Pencil />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Edit unit</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Units" />

            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="max-w-10xl mx-auto flex w-full flex-1 flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div className="space-y-1">
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Units
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                View and manage the units in your organization.
                            </p>
                        </div>

                        <Deferred
                            data="properties"
                            fallback={
                                <Button className="w-full sm:w-auto" disabled>
                                    <Spinner />
                                    Create unit
                                </Button>
                            }
                        >
                            <CreateUnitDialogue
                                properties={properties ?? []}
                                unitStatuses={unitStatuses}
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

            {unitBeingEdited && properties && (
                <EditUnitDialogue
                    key={unitBeingEdited.id}
                    unit={unitBeingEdited}
                    properties={properties}
                    unitStatuses={unitStatuses}
                    open
                    onOpenChange={(open) => {
                        if (!open) {
                            setUnitBeingEdited(null);
                        }
                    }}
                />
            )}
        </>
    );
}
