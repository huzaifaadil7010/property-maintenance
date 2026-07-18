import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect, useMemo } from 'react';
import { toast } from 'sonner';

export default function AppLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {

    const page = usePage()
    const flash = useMemo(()=> page.flash, [page.flash]);

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success)
            delete flash.success

            return;
        }

        if (flash?.error) {
            toast.error(flash.error)
            delete flash.error;

            return;
        }

    }, [flash])

    return (
        <AppLayoutTemplate breadcrumbs={breadcrumbs}>
            {children}
        </AppLayoutTemplate>
    );
}
