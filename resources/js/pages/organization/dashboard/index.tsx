import { Deferred, Head } from '@inertiajs/react';
import {
    Building2,
    CircleAlert,
    CircleCheckBig,
    Clock3,
    DoorOpen,
    KeyRound,
    UserRoundCheck,
    UsersRound,
    Wrench,
} from 'lucide-react';
import CreatePropertyDialogue from '@/components/organization/common/create-property-dialogue';
import CreateUnitDialogue from '@/components/organization/common/create-unit-dialogue';
import type {
    PropertyOption,
    UnitStatusOption,
} from '@/components/organization/common/create-unit-dialogue';
import { StatCard } from '@/components/organization/dashboard/stat-card';
import { StatCardSkeleton } from '@/components/organization/dashboard/stat-card-skeleton';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/wayfinder/routes';

type DashboardProps = {
    properties?: PropertyOption[];
    totalActiveResidents: number;
    totalAvailableTechnicians: number;
    totalCompletedRequests: number;
    totalInProgressRequests: number;
    totalOccupiedUnits: number;
    totalOpenRequests: number;
    totalProperties: number;
    totalUnits: number;
    totalVacantUnits: number;
    unitStatuses: UnitStatusOption[];
};

export default function Dashboard({
    properties,
    totalActiveResidents,
    totalAvailableTechnicians,
    totalCompletedRequests,
    totalInProgressRequests,
    totalOccupiedUnits,
    totalOpenRequests,
    totalProperties,
    totalUnits,
    totalVacantUnits,
    unitStatuses,
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

                    <div className="flex flex-col gap-2 sm:flex-row">
                        <CreatePropertyDialogue
                            only={['totalProperties', 'properties']}
                        />
                        <Deferred
                            data="properties"
                            fallback={
                                <Button className="w-full sm:w-auto" disabled>
                                    <Spinner />
                                    Create unit
                                </Button>
                            }
                        >
                            <CreateUnitDialogue
                                properties={properties ?? []}
                                unitStatuses={unitStatuses}
                                only={[
                                    'totalUnits',
                                    'totalVacantUnits',
                                    'totalOccupiedUnits',
                                ]}
                            />
                        </Deferred>
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 min-[90.0625rem]:grid-cols-5">
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

                    <Deferred
                        data="totalActiveResidents"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="Total active residents"
                            value={totalActiveResidents}
                            description="Residents with an active occupancy"
                            icon={UserRoundCheck}
                        />
                    </Deferred>

                    <Deferred
                        data="totalAvailableTechnicians"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="Available technicians"
                            value={totalAvailableTechnicians}
                            description="Technicians currently available"
                            icon={Wrench}
                        />
                    </Deferred>

                    <Deferred
                        data="totalOpenRequests"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="Open requests"
                            value={totalOpenRequests}
                            description="Awaiting review or assignment"
                            icon={CircleAlert}
                        />
                    </Deferred>

                    <Deferred
                        data="totalInProgressRequests"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="In-progress requests"
                            value={totalInProgressRequests}
                            description="Maintenance currently underway"
                            icon={Clock3}
                        />
                    </Deferred>

                    <Deferred
                        data="totalCompletedRequests"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="Completed requests"
                            value={totalCompletedRequests}
                            description="Maintenance work completed"
                            icon={CircleCheckBig}
                        />
                    </Deferred>
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
