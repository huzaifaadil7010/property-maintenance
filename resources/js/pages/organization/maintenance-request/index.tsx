import { Head, router, usePage } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { format } from 'date-fns';
import debounce from 'lodash.debounce';
import { Eye } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Input } from '@/components/ui/input';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { show } from '@/wayfinder/App/Http/Controllers/Organization/MaintenanceRequestsController';
import type { Inertia } from '@/wayfinder/types';

type GeneratedPageProps = Inertia.Pages.Organization.MaintenanceRequest.Index;

type EnumOption = {
    label: string;
    value: string;
};

type RelatedUser = {
    id: number;
    name: string;
};

type MaintenanceRequestTableRow = {
    id: number;
    title: string;
    property: RelatedUser;
    unit: RelatedUser;
    resident: RelatedUser;
    assigned_technician: RelatedUser | null;
    category: EnumOption;
    priority: EnumOption;
    status: EnumOption;
    created_at: string | null;
};

export default function Index(props: GeneratedPageProps) {
    const { url, props: pageProps } = usePage();
    const maintenanceRequests = props.maintenanceRequests as unknown as {
        data: MaintenanceRequestTableRow[];
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

        const reloadMaintenanceRequests = debounce(() => {
            router.reload({
                data: { search, page: 1 },
                only: ['maintenanceRequests'],
            });
        }, 300);

        reloadMaintenanceRequests();

        return () => reloadMaintenanceRequests.cancel();
    }, [search]);

    function onView(maintenanceRequest: MaintenanceRequestTableRow) {
        if (!pageProps.currentOrganization) {
            return;
        }

        router.visit(
            show({
                organization: pageProps.currentOrganization.uuid,
                maintenanceRequest: maintenanceRequest.id,
            })
        );
    }

    const columns: ColumnDef<MaintenanceRequestTableRow>[] = [
        {
            accessorKey: 'title',
            header: 'Title',
        },
        {
            accessorKey: 'property.name',
            header: 'Property',
        },
        {
            accessorKey: 'unit.name',
            header: 'Unit',
        },
        {
            accessorKey: 'resident.name',
            header: 'Resident',
        },
        {
            accessorKey: 'assigned_technician.name',
            header: 'Technician',
            cell: ({ row }) => row.original.assigned_technician?.name ?? '—',
        },
        {
            accessorKey: 'category.label',
            header: 'Category',
        },
        {
            accessorKey: 'priority.label',
            header: 'Priority',
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
                    ? format(row.original.created_at, 'yyyy-MM-dd')
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
                                aria-label="View maintenance request"
                                onClick={() => onView(row.original)}
                            >
                                <Eye />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>View request</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            ),
        },
    ];

    return (
        <>
            <Head title="Maintenance Requests" />

            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="max-w-10xl mx-auto flex w-full flex-1 flex-col gap-6">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Maintenance Requests
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            View and manage maintenance requests in your
                            organization.
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
                                placeholder="Search maintenance requests..."
                                aria-label="Search maintenance requests"
                                className="w-full sm:max-w-sm"
                            />
                        </div>

                        <DataTable
                            columns={columns}
                            data={maintenanceRequests.data}
                            pagination={{
                                currentPage:
                                    maintenanceRequests.meta.current_page,
                                lastPage: maintenanceRequests.meta.last_page,
                                perPage:
                                    requestedPerPage ||
                                    maintenanceRequests.meta.per_page,
                                total: maintenanceRequests.meta.total,
                                onChange: (page, perPage) => {
                                    router.reload({
                                        data: { page, perPage },
                                        only: ['maintenanceRequests'],
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
