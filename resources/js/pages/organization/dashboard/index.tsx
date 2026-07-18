import { Deferred, Head } from '@inertiajs/react';
import {
    Building2,
    CircleAlert,
    CircleCheckBig,
    Clock3,
    DoorOpen,
    KeyRound,
    UsersRound,
} from 'lucide-react';
import CreatePropertyDialogue from '@/components/organization/common/create-property-dialogue';
import { StatCard } from '@/components/organization/dashboard/stat-card';
import { StatCardSkeleton } from '@/components/organization/dashboard/stat-card-skeleton';
import { dashboard } from '@/wayfinder/routes';

type DashboardProps = {
    totalOccupiedUnits: number;
    totalProperties: number;
    totalUnits: number;
    totalVacantUnits: number;
};

export default function Dashboard({
    totalOccupiedUnits,
    totalProperties,
    totalUnits,
    totalVacantUnits,
}: DashboardProps) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="grid gap-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Organization dashboard
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            An overview of your properties, units, and
                            maintenance activity.
                        </p>
                    </div>

                    <CreatePropertyDialogue only={['totalProperties']} />
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
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

                    <Deferred
                        data="totalVacantUnits"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="Total vacant units"
                            value={totalVacantUnits}
                            description="Units currently available"
                            icon={KeyRound}
                        />
                    </Deferred>

                    <Deferred
                        data="totalOccupiedUnits"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="Total occupied units"
                            value={totalOccupiedUnits}
                            description="Units currently occupied"
                            icon={UsersRound}
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
