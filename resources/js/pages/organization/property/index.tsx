import { Head, router, useForm, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { Pencil, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import ConfirmationDialogue from '@/components/organization/common/confirmation-dialogue';
import CreatePropertyDialogue from '@/components/organization/common/create-property-dialogue';
import EditPropertyDialogue from '@/components/organization/common/edit-property-dialogue';
import type { EditableProperty } from '@/components/organization/common/edit-property-dialogue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import { PageHeader } from '@/components/ui/page-header';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { destroy } from '@/wayfinder/App/Http/Controllers/Organization/PropertiesController';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.Property.Index;

type PropertyTableRow = EditableProperty & {
    created_at: unknown;
    units_count?: number;
};

export default function Index(props: GeneratedPageProps) {
    const { url, props: pageProps } = usePage();
    const properties = props.properties as unknown as {
        data: PropertyTableRow[];
        meta: Record<
            'current_page' | 'last_page' | 'per_page' | 'total',
            number
        >;
    };
    const queryParameters = new URLSearchParams(url.split('?')[1] ?? '');
    const requestedPerPage = Number(queryParameters.get('perPage'));
    const [search, setSearch] = useState(queryParameters.get('search') ?? '');
    const [propertyBeingEdited, setPropertyBeingEdited] =
        useState<PropertyTableRow | null>(null);
    const [propertyBeingDeleted, setPropertyBeingDeleted] =
        useState<PropertyTableRow | null>(null);
    const deleteForm = useForm({});
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

    function onEdit(property: PropertyTableRow) {
        setPropertyBeingEdited(property);
    }

    function onDelete(property: PropertyTableRow) {
        setPropertyBeingDeleted(property);
    }

    function deleteProperty() {
        if (!pageProps.currentOrganization || !propertyBeingDeleted) {
            return;
        }

        deleteForm.submit(
            destroy({
                organization: pageProps.currentOrganization.uuid,
                property: propertyBeingDeleted.id,
            }),
            {
                only: ['properties'],
                preserveScroll: true,
                onSuccess: () => setPropertyBeingDeleted(null),
            },
        );
    }

    const columns: ColumnDef<PropertyTableRow>[] = [
        {
            accessorKey: 'name',
            header: 'Name',
            cell: ({ row }) => (
                <div className="max-w-xs wrap-anywhere whitespace-normal">
                    {row.original.name}
                </div>
            ),
        },
        {
            accessorKey: 'type.label',
            header: 'Type',
        },
        {
            accessorKey: 'city',
            header: 'City',
            cell: ({ row }) => (
                <div className="max-w-xs wrap-anywhere whitespace-normal">
                    {row.original.city}
                </div>
            ),
        },
        {
            accessorKey: 'address',
            header: 'Address',
            cell: ({ row }) => (
                <div className="max-w-xs wrap-anywhere whitespace-normal">
                    {row.original.address}
                </div>
            ),
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
                                aria-label="Edit property"
                                onClick={() => onEdit(row.original)}
                            >
                                <Pencil />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Edit property</p>
                        </TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="size-8 text-destructive hover:text-destructive"
                                aria-label="Delete property"
                                onClick={() => onDelete(row.original)}
                            >
                                <Trash2 />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Delete property</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Properties" />

            <div className="page-container">
                <PageHeader
                    eyebrow="Portfolio"
                    title="Properties"
                    description="View and manage the properties in your organization."
                    actions={<CreatePropertyDialogue only={['properties']} />}
                />

                <div className="surface-card flex min-h-0 flex-1 flex-col overflow-hidden">
                    <div className="flex w-full justify-end border-b border-border/70 bg-muted/20 p-3 sm:p-4">
                        <Input
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Search properties..."
                            aria-label="Search properties"
                            className="w-full sm:max-w-sm"
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        data={properties.data}
                        renderMobileCard={(property) => (
                            <div className="interactive-card grid gap-4 rounded-2xl border bg-card p-4">
                                <div>
                                    <p className="font-semibold">
                                        {property.name}
                                    </p>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {property.address}, {property.city}
                                    </p>
                                </div>
                                <div className="flex items-center justify-between gap-3 border-t border-border/70 pt-3">
                                    <p className="text-sm text-muted-foreground">
                                        {property.units_count ?? 0} units
                                    </p>
                                    <div className="flex gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="size-8"
                                            onClick={() => onEdit(property)}
                                            aria-label="Edit property"
                                        >
                                            <Pencil />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="size-8 text-destructive"
                                            onClick={() => onDelete(property)}
                                            aria-label="Delete property"
                                        >
                                            <Trash2 />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        )}
                        pagination={{
                            currentPage: properties.meta.current_page,
                            lastPage: properties.meta.last_page,
                            perPage:
                                requestedPerPage || properties.meta.per_page,
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

            {propertyBeingEdited && (
                <EditPropertyDialogue
                    key={propertyBeingEdited.id}
                    property={propertyBeingEdited}
                    open
                    onOpenChange={(open) => {
                        if (!open) {
                            setPropertyBeingEdited(null);
                        }
                    }}
                />
            )}

            {propertyBeingDeleted && (
                <ConfirmationDialogue
                    open
                    onOpenChange={(open) => {
                        if (!open) {
                            setPropertyBeingDeleted(null);
                        }
                    }}
                    onSubmit={deleteProperty}
                    title="Delete property"
                    description={
                        <>
                            Are you sure you want to delete{' '}
                            <span className="font-medium text-foreground">
                                {propertyBeingDeleted.name}
                            </span>
                            ? This action cannot be undone.
                        </>
                    }
                    submitLabel="Delete property"
                    submittingLabel="Deleting property"
                    submitVariant="destructive"
                    processing={deleteForm.processing}
                    disabled={pageProps.currentOrganization === null}
                />
            )}
        </>
    );
}
