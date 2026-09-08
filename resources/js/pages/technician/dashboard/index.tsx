import { Head } from '@inertiajs/react';
import { dashboard } from '@/wayfinder/routes/technician';

export default function Dashboard() {
    return (
        <>
            <Head title="Technician Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="grid gap-1">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Hello technician
                    </h1>
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: dashboard().url }],
};
