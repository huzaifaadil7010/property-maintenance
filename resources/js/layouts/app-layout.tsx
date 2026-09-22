import { usePage } from '@inertiajs/react';
import { AlertTriangle } from 'lucide-react';
import { useEffect, useMemo } from 'react';
import { toast } from 'sonner';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';

export default function AppLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    const page = usePage();
    const { subscriptionAccess } = page.props;
    const flash = useMemo(
        () => page.flash as { success?: string; error?: string },
        [page.flash],
    );

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);

            return;
        }

        if (flash?.error) {
            toast.error(flash.error);

            return;
        }
    }, [flash]);

    return (
        <AppLayoutTemplate breadcrumbs={breadcrumbs}>
            {subscriptionAccess.message && (
                <div className="mx-auto w-full max-w-[1600px] px-4 pt-4 md:px-6 md:pt-6 lg:px-8 lg:pt-8">
                    <Alert className="border-amber-300 bg-amber-50 text-amber-950">
                        <AlertTriangle />
                        <AlertTitle>Subscription attention required</AlertTitle>
                        <AlertDescription>
                            {subscriptionAccess.message}
                        </AlertDescription>
                    </Alert>
                </div>
            )}
            {children}
        </AppLayoutTemplate>
    );
}
