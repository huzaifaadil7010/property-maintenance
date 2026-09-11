import { Deferred, Head, router, useForm, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { Pencil, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import ConfirmationDialogue from '@/components/organization/common/confirmation-dialogue';
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
import { PageHeader } from '@/components/ui/page-header';
import { Spinner } from '@/components/ui/spinner';
import { StatusBadge } from '@/components/ui/status-badge';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { destroy } from '@/wayfinder/App/Http/Controllers/Organization/UnitsController';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.Unit.Index;

type UnitTableRow = EditableUnit & {
    created_at: unknown;
};

export default function Index(props: GeneratedPageProps) {
    const { url, props: pageProps } = usePage();
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
    const [unitBeingDeleted, setUnitBeingDeleted] =
        useState<UnitTableRow | null>(null);
    const deleteForm = useForm({});
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

    function onDelete(unit: UnitTableRow) {
        setUnitBeingDeleted(unit);
    }

    function deleteUnit() {
        if (!pageProps.currentOrganization || !unitBeingDeleted) {
            return;
        }

        deleteForm.submit(
            destroy({
                organization: pageProps.currentOrganization.uuid,
                unit: unitBeingDeleted.id,
            }),
            {
                only: ['units'],
                preserveScroll: true,
                onSuccess: () => setUnitBeingDeleted(null),
            },
        );
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
            cell: ({ row }) => (
                <StatusBadge option={row.original.status} kind="unit" />
            ),
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
                <div className="flex justify-end gap-1">
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
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="size-8 text-destructive hover:text-destructive"
                                aria-label="Delete unit"
                                onClick={() => onDelete(row.original)}
                            >
                                <Trash2 />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Delete unit</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Units" />

            <div className="page-container">
                <PageHeader
                    eyebrow="Portfolio"
                    title="Units"
                    description="View and manage the units in your organization."
                    actions={
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
                                only={['units']}
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
                            placeholder="Search units..."
                            aria-label="Search units"
                            className="w-full sm:max-w-sm"
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={units.data}
                        renderMobileCard={(unit) => (
                            <div className="interactive-card grid gap-4 rounded-2xl border bg-card p-4">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="font-semibold">
                                            Unit {unit.name}
                                        </p>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {unit.property.name}
                                            {unit.floor
                                                ? ` · Floor ${unit.floor}`
                                                : ''}
                                        </p>
                                    </div>
                                    <StatusBadge
                                        option={unit.status}
                                        kind="unit"
                                    />
                                </div>
                                <div className="flex justify-end gap-1 border-t border-border/70 pt-3">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="size-8"
                                        onClick={() => onEdit(unit)}
                                        aria-label="Edit unit"
                                    >
                                        <Pencil />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="size-8 text-destructive"
                                        onClick={() => onDelete(unit)}
                                        aria-label="Delete unit"
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>
                            </div>
                        )}
                        pagination={{
                            currentPage: units.meta.current_page,
                            lastPage: units.meta.last_page,
                            perPage: requestedPerPage || units.meta.per_page,
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

            {unitBeingDeleted && (
                <ConfirmationDialogue
                    open
                    onOpenChange={(open) => {
                        if (!open) {
                            setUnitBeingDeleted(null);
                        }
                    }}
                    onSubmit={deleteUnit}
                    title="Delete unit"
                    description={
                        <>
                            Are you sure you want to delete{' '}
                            <span className="font-medium text-foreground">
                                {unitBeingDeleted.name}
                            </span>
                            ? This action cannot be undone.
                        </>
                    }
                    submitLabel="Delete unit"
                    submittingLabel="Deleting unit"
                    submitVariant="destructive"
                    processing={deleteForm.processing}
                    disabled={pageProps.currentOrganization === null}
                />
            )}
        </>
    );
}
