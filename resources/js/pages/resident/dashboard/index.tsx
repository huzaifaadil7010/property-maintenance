import { Head } from '@inertiajs/react';

export default function Dashboard() {
    return (
        <>
            <Head title="Resident Dashboard" />

            <div className="flex min-h-0 flex-1 items-center justify-center p-6">
                <div className="rounded-xl border bg-card px-6 py-10 text-center shadow-sm">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Resident dashboard
                    </h1>
                    <p className="mt-2 text-sm text-muted-foreground">
                        resident dashboard
                    </p>
                </div>
            </div>
        </>
    );
}
