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
        <div className="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div className="shrink-0 text-sm whitespace-nowrap text-muted-foreground">
                {totalRows} row(s) total.
            </div>
            <div className="grid w-full grid-cols-[minmax(0,1fr)_auto] items-center gap-3 sm:flex sm:w-auto sm:flex-nowrap sm:justify-end sm:gap-6 xl:gap-8">
                <div className="col-span-2 flex shrink-0 items-center gap-2 sm:col-auto">
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
                <div className="flex shrink-0 items-center justify-start text-sm font-medium whitespace-nowrap sm:w-[100px] sm:justify-center">
                    Page {currentPage} of {lastPage}
                </div>
                <div className="flex shrink-0 items-center justify-end gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        className="hidden size-8 lg:flex"
                        onClick={() => table.setPageIndex(0)}
                        disabled={currentPage <= 1}
                    >
                        <span className="sr-only">Go to first page</span>
                        <ChevronsLeft />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="size-8"
                        onClick={() => table.setPageIndex(currentPage - 2)}
                        disabled={currentPage <= 1}
                    >
                        <span className="sr-only">Go to previous page</span>
                        <ChevronLeft />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="size-8"
                        onClick={() => table.setPageIndex(currentPage)}
                        disabled={currentPage >= lastPage}
                    >
                        <span className="sr-only">Go to next page</span>
                        <ChevronRight />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="hidden size-8 lg:flex"
                        onClick={() => table.setPageIndex(lastPage - 1)}
                        disabled={currentPage >= lastPage}
                    >
                        <span className="sr-only">Go to last page</span>
                        <ChevronsRight />
                    </Button>
                </div>
            </div>
        </div>
    );
}
