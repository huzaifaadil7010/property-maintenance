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
import { PageHeader } from '@/components/ui/page-header';
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
            <div className="page-container">
                <PageHeader
                    eyebrow="Workspace overview"
                    title="Organization dashboard"
                    description="A clear view of your portfolio, people, and maintenance activity."
                    actions={
                        <div className="flex flex-col gap-2 sm:flex-row">
                            <CreatePropertyDialogue
                                only={['totalProperties', 'properties']}
                            />
                            <Deferred
                                data="properties"
                                fallback={
                                    <Button
                                        className="w-full sm:w-auto"
                                        disabled
                                    >
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
                    }
                />

                <section className="grid gap-3">
                    <div className="flex items-center gap-3">
                        <h2 className="text-sm font-semibold">Portfolio</h2>
                        <div className="h-px flex-1 bg-border/80" />
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <Deferred
                            data="totalProperties"
                            fallback={<StatCardSkeleton />}
                        >
                            <StatCard
                                title="Total properties"
                                value={totalProperties}
                                description="Properties in this organization"
                                icon={Building2}
                                tone="brand"
                            />
                        </Deferred>

                        <Deferred
                            data="totalUnits"
                            fallback={<StatCardSkeleton />}
                        >
                            <StatCard
                                title="Total units"
                                value={totalUnits}
                                description="Units across all properties"
                                icon={DoorOpen}
                                tone="brand"
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
                                tone="sky"
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
                                tone="emerald"
                            />
                        </Deferred>
                    </div>
                </section>

                <section className="grid gap-3">
                    <div className="flex items-center gap-3">
                        <h2 className="text-sm font-semibold">People</h2>
                        <div className="h-px flex-1 bg-border/80" />
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Deferred
                            data="totalActiveResidents"
                            fallback={<StatCardSkeleton />}
                        >
                            <StatCard
                                title="Total active residents"
                                value={totalActiveResidents}
                                description="Residents with an active occupancy"
                                icon={UserRoundCheck}
                                tone="emerald"
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
                                tone="sky"
                            />
                        </Deferred>
                    </div>
                </section>

                <section className="grid gap-3">
                    <div className="flex items-center gap-3">
                        <h2 className="text-sm font-semibold">Maintenance</h2>
                        <div className="h-px flex-1 bg-border/80" />
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <Deferred
                            data="totalOpenRequests"
                            fallback={<StatCardSkeleton />}
                        >
                            <StatCard
                                title="Open requests"
                                value={totalOpenRequests}
                                description="Awaiting review or assignment"
                                icon={CircleAlert}
                                tone="amber"
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
                                tone="violet"
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
                                tone="emerald"
                            />
                        </Deferred>
                    </div>
                </section>
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
