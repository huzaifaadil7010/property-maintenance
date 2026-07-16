import { Deferred, Head } from '@inertiajs/react';
import {
    Building2,
    CircleAlert,
    CircleCheckBig,
    Clock3,
    DoorOpen,
} from 'lucide-react';
import { StatCard } from '@/components/organization/dashboard/stat-card';
import { StatCardSkeleton } from '@/components/organization/dashboard/stat-card-skeleton';
import { dashboard } from '@/wayfinder/routes';

type DashboardProps = {
    totalProperties: number;
    totalUnits: number;
};

export default function Dashboard({
    totalProperties,
    totalUnits,
}: DashboardProps) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="grid gap-1">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Organization dashboard
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        An overview of your properties, units, and maintenance
                        activity.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    <Deferred
                        data="totalProperties"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="Total properties"
                            value={totalProperties}
                            description="Properties in this organization"
                            icon={Building2}
                        />
                    </Deferred>

                    <Deferred data="totalUnits" fallback={<StatCardSkeleton />}>
                        <StatCard
                            title="Total units"
                            value={totalUnits}
                            description="Units across all properties"
                            icon={DoorOpen}
                        />
                    </Deferred>

                    <StatCard
                        title="Open requests"
                        value={0}
                        description="Awaiting review or assignment"
                        icon={CircleAlert}
                    />
                    <StatCard
                        title="In-progress requests"
                        value={0}
                        description="Maintenance currently underway"
                        icon={Clock3}
                    />
                    <StatCard
                        title="Completed requests"
                        value={0}
                        description="Maintenance work completed"
                        icon={CircleCheckBig}
                    />
                </div>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
