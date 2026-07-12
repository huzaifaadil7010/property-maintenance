import type { Table } from '@tanstack/react-table';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface DataTablePaginationProps<TData> {
    table: Table<TData>;
    currentPage: number;
    lastPage: number;
    perPage: number;
    totalRows: number;
}

export function DataTablePagination<TData>({
    table,
    currentPage,
    lastPage,
    perPage,
    totalRows,
}: DataTablePaginationProps<TData>) {
    return (
        <div className="flex flex-col gap-4 px-2 xl:flex-row xl:items-center xl:justify-between">
            <div className="shrink-0 text-sm whitespace-nowrap text-muted-foreground">
                {totalRows} row(s) total.
            </div>
            <div className="flex w-full flex-wrap items-center justify-between gap-4 xl:w-auto xl:flex-nowrap xl:justify-end xl:gap-8">
                <div className="flex shrink-0 items-center gap-2">
                    <p className="text-sm font-medium whitespace-nowrap">
                        Rows per page
                    </p>
                    <Select
                        key={perPage}
                        value={`${perPage}`}
                        onValueChange={(value) => {
                            table.setPageSize(Number(value));
                        }}
                    >
                        <SelectTrigger className="h-8 w-[70px]">
                            <SelectValue placeholder={perPage} />
                        </SelectTrigger>
                        <SelectContent side="top">
                            {[10, 20, 25, 30, 40, 50].map((pageSize) => (
                                <SelectItem
                                    key={pageSize}
                                    value={`${pageSize}`}
                                >
                                    {pageSize}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="flex w-[100px] shrink-0 items-center justify-center text-sm font-medium whitespace-nowrap">
                    Page {currentPage} of {lastPage}
                </div>
                <div className="flex shrink-0 items-center gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        className="hidden size-8 lg:flex"
                        onClick={() => table.setPageIndex(0)}
                        disabled={!table.getCanPreviousPage()}
                    >
                        <span className="sr-only">Go to first page</span>
                        <ChevronsLeft />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="size-8"
                        onClick={() => table.previousPage()}
                        disabled={!table.getCanPreviousPage()}
                    >
                        <span className="sr-only">Go to previous page</span>
                        <ChevronLeft />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="size-8"
                        onClick={() => table.nextPage()}
                        disabled={!table.getCanNextPage()}
                    >
                        <span className="sr-only">Go to next page</span>
                        <ChevronRight />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="hidden size-8 lg:flex"
                        onClick={() =>
                            table.setPageIndex(table.getPageCount() - 1)
                        }
                        disabled={!table.getCanNextPage()}
                    >
                        <span className="sr-only">Go to last page</span>
                        <ChevronsRight />
                    </Button>
                </div>
            </div>
        </div>
    );
}
