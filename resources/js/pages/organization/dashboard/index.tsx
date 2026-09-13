import { Deferred, Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    ArrowRight,
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
import { ActivityFeed } from '@/components/organization/activity-log/activity-feed';
import type { ActivityLogItem } from '@/components/organization/activity-log/activity-feed';
import { ActivityFeedSkeleton } from '@/components/organization/activity-log/activity-feed-skeleton';
import CreatePropertyDialogue from '@/components/organization/common/create-property-dialogue';
import CreateUnitDialogue from '@/components/organization/common/create-unit-dialogue';
import type {
    PropertyOption,
    UnitStatusOption,
} from '@/components/organization/common/create-unit-dialogue';
import { StatCard } from '@/components/organization/dashboard/stat-card';
import { StatCardSkeleton } from '@/components/organization/dashboard/stat-card-skeleton';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import { PageHeader } from '@/components/ui/page-header';
import { Spinner } from '@/components/ui/spinner';
import { index as activityLogsIndex } from '@/wayfinder/App/Http/Controllers/Organization/ActivityLogsController';
import { dashboard } from '@/wayfinder/routes';

type DashboardProps = {
    properties?: PropertyOption[];
    recentActivityLogs?: {
        data: ActivityLogItem[];
    };
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
    recentActivityLogs,
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
    const { currentOrganization } = usePage().props;

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
                                only={[
                                    'totalProperties',
                                    'properties',
                                    'recentActivityLogs',
                                ]}
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
                                        'recentActivityLogs',
                                    ]}
                                />
                            </Deferred>
                        </div>
                    }
                />

                <div className="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(22rem,24rem)]">
                    <div className="grid gap-6">
                        <section className="grid gap-3">
                            <div className="flex items-center gap-3">
                                <h2 className="text-sm font-semibold">
                                    Portfolio
                                </h2>
                                <div className="h-px flex-1 bg-border/80" />
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">
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
                                <h2 className="text-sm font-semibold">
                                    People
                                </h2>
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
                                <h2 className="text-sm font-semibold">
                                    Maintenance
                                </h2>
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

                    <aside className="surface-card p-5 sm:p-6 xl:sticky xl:top-6">
                        <div className="mb-6 flex items-start justify-between gap-4">
                            <div>
                                <p className="text-sm font-semibold">
                                    Recent activity
                                </p>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Latest changes across your organization
                                </p>
                            </div>
                            <div className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <Activity
                                    className="size-4.5"
                                    aria-hidden="true"
                                />
                            </div>
                        </div>

                        <Deferred
                            data="recentActivityLogs"
                            fallback={<ActivityFeedSkeleton />}
                        >
                            {(recentActivityLogs?.data.length ?? 0) > 0 ? (
                                <ActivityFeed
                                    activities={recentActivityLogs?.data ?? []}
                                />
                            ) : (
                                <EmptyState
                                    icon={Activity}
                                    title="No activity yet"
                                    description="New organization activity will appear here."
                                />
                            )}
                        </Deferred>

                        <div className="mt-6 border-t border-border pt-4">
                            {currentOrganization ? (
                                <Button
                                    asChild
                                    variant="outline"
                                    className="w-full"
                                >
                                    <Link
                                        href={
                                            activityLogsIndex(
                                                currentOrganization.uuid,
                                            ).url
                                        }
                                    >
                                        View all activity
                                        <ArrowRight />
                                    </Link>
                                </Button>
                            ) : (
                                <Button
                                    variant="outline"
                                    className="w-full"
                                    disabled
                                >
                                    View all activity
                                    <ArrowRight />
                                </Button>
                            )}
                        </div>
                    </aside>
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
