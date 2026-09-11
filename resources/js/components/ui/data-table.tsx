'use client';

import {
    flexRender,
    getCoreRowModel,
    useReactTable,
} from '@tanstack/react-table';
import type { ColumnDef, PaginationState } from '@tanstack/react-table';

import { DataTablePagination } from '@/components/ui/data-table-pagination';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface DataTableProps<TData, TValue> {
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    pagination?: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
        onChange: (page: number, perPage: number) => void;
    };
    renderMobileCard?: (row: TData) => React.ReactNode;
}

export function DataTable<TData, TValue>({
    columns,
    data,
    pagination,
    renderMobileCard,
}: DataTableProps<TData, TValue>) {
    const paginationState: PaginationState = {
        pageIndex: (pagination?.currentPage ?? 1) - 1,
        pageSize: pagination?.perPage ?? data.length,
    };

    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        manualPagination: Boolean(pagination),
        pageCount: pagination?.lastPage,
        state: pagination ? { pagination: paginationState } : undefined,
        onPaginationChange: pagination
            ? (updater) => {
                  const nextPagination =
                      typeof updater === 'function'
                          ? updater(paginationState)
                          : updater;

                  const page =
                      nextPagination.pageSize === paginationState.pageSize
                          ? nextPagination.pageIndex + 1
                          : 1;

                  pagination.onChange(page, nextPagination.pageSize);
              }
            : undefined,
    });

    return (
        <div className="flex min-h-0 flex-1 flex-col">
            {renderMobileCard && (
                <div className="grid gap-3 md:hidden">
                    {data.length ? (
                        data.map((row, index) => (
                            <div key={index}>{renderMobileCard(row)}</div>
                        ))
                    ) : (
                        <div className="rounded-2xl border border-dashed bg-card px-6 py-12 text-center text-sm text-muted-foreground">
                            No results found.
                        </div>
                    )}
                </div>
            )}
            <div
                className={cn(
                    'overflow-hidden rounded-2xl border border-border/90 bg-card shadow-[0_1px_2px_rgba(20,45,32,0.03)]',
                    renderMobileCard && 'hidden md:block',
                )}
            >
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(
                                                      header.column.columnDef
                                                          .header,
                                                      header.getContext(),
                                                  )}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow
                                    key={row.id}
                                    data-state={
                                        row.getIsSelected() && 'selected'
                                    }
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>
                                            {flexRender(
                                                cell.column.columnDef.cell,
                                                cell.getContext(),
                                            )}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell
                                    colSpan={columns.length}
                                    className="h-24 text-center"
                                >
                                    No results found.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            {pagination && (
                <div className="mt-auto flex flex-col gap-4 pt-6 pb-2">
                    <Separator />
                    <DataTablePagination
                        table={table}
                        currentPage={pagination.currentPage}
                        lastPage={pagination.lastPage}
                        perPage={pagination.perPage}
                        totalRows={pagination.total}
                    />
                </div>
            )}
        </div>
    );
}
