import { Head } from '@inertiajs/react';
import type { ColumnDef } from '@tanstack/react-table';
import { DataTable } from '@/components/ui/data-table';
import type { App, Inertia } from '@/wayfinder/types';
import { format } from 'date-fns';

type GeneratedPageProps = Inertia.Pages.Organization.Property.Index;

export default function Index(props: GeneratedPageProps) {
    const columns: ColumnDef<App.Models.Property>[] = [
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
            accessorKey: 'created_at',
            header: 'Created At',
            cell: ({ row }) => format(row.original.created_at, 'yyyy-MM-dd'),
        },
    ];

    return (
        <>
            <Head title="Properties" />

            <div className="flex flex-1 p-4 md:p-6 lg:p-8">
                <div className="max-w-10xl mx-auto w-full space-y-6">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Properties
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            View and manage the properties in your organization.
                        </p>
                    </div>

                    <div className="rounded-xl bg-card shadow-sm">
                        <DataTable
                            columns={columns}
                            data={props.properties.data}
                        />
                    </div>
                </div>
            </div>
        </>
    );
}
