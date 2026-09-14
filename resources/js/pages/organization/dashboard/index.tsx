import { Deferred, Head, Link, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import {
    Activity,
    ArrowRight,
    Building2,
    CircleAlert,
    CircleCheckBig,
    ClipboardList,
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
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { EmptyState } from '@/components/ui/empty-state';
import { PageHeader } from '@/components/ui/page-header';
import { Skeleton } from '@/components/ui/skeleton';
import { Spinner } from '@/components/ui/spinner';
import { StatusBadge } from '@/components/ui/status-badge';
import { index as activityLogsIndex } from '@/wayfinder/App/Http/Controllers/Organization/ActivityLogsController';
import { dashboard } from '@/wayfinder/routes';
import { maintenanceRequests } from '@/wayfinder/routes/organization';

type EnumOption = {
    label: string;
    value: string;
};

type MaintenanceRequestNeedingAttention = {
    id: number;
    title: string;
    property: { id: number; name: string };
    unit: { id: number; name: string };
    category: EnumOption;
    priority: EnumOption;
    status: EnumOption;
    created_at: string | null;
};

type DashboardProps = {
    maintenanceRequestsNeedingAttention?: {
        data: MaintenanceRequestNeedingAttention[];
    };
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
    maintenanceRequestsNeedingAttention,
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

                        <Card className="overflow-hidden">
                            <CardHeader className="flex-row items-start justify-between gap-4">
                                <div className="grid gap-1">
                                    <CardTitle>
                                        Requests needing attention
                                    </CardTitle>
                                    <CardDescription>
                                        The five newest requests awaiting review
                                        or assignment.
                                    </CardDescription>
                                </div>
                                {currentOrganization ? (
                                    <Button variant="ghost" size="sm" asChild>
                                        <Link
                                            href={
                                                maintenanceRequests(
                                                    currentOrganization.uuid,
                                                ).url
                                            }
                                            prefetch
                                        >
                                            View all
                                            <ArrowRight aria-hidden="true" />
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button variant="ghost" size="sm" disabled>
                                        View all
                                        <ArrowRight aria-hidden="true" />
                                    </Button>
                                )}
                            </CardHeader>
                            <CardContent>
                                <Deferred
                                    data="maintenanceRequestsNeedingAttention"
                                    fallback={
                                        <RequestsNeedingAttentionSkeleton />
                                    }
                                >
                                    <RequestsNeedingAttention
                                        requests={
                                            maintenanceRequestsNeedingAttention?.data ??
                                            []
                                        }
                                    />
                                </Deferred>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="grid gap-6">
                        <aside className="surface-card p-5 sm:p-6">
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
                                        activities={
                                            recentActivityLogs?.data ?? []
                                        }
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

                        <section className="grid gap-3">
                            <div className="flex items-center gap-3">
                                <h2 className="text-sm font-semibold">
                                    People
                                </h2>
                                <div className="h-px flex-1 bg-border/80" />
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
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
                    </div>
                </div>
            </div>
        </>
    );
}

function RequestsNeedingAttention({
    requests,
}: {
    requests: MaintenanceRequestNeedingAttention[];
}) {
    if (requests.length === 0) {
        return (
            <EmptyState
                icon={ClipboardList}
                title="No requests need attention"
                description="Open or reopened maintenance requests will appear here."
            />
        );
    }

    return (
        <div className="divide-y overflow-hidden rounded-2xl border border-border/90">
            {requests.map((request) => (
                <div
                    key={request.id}
                    className="grid gap-3 p-4 transition-colors hover:bg-accent/45 sm:grid-cols-[1fr_auto]"
                >
                    <div className="grid gap-1">
                        <p className="font-medium">{request.title}</p>
                        <p className="text-sm text-muted-foreground">
                            {request.property.name} · Unit {request.unit.name}
                            {request.created_at
                                ? ` · ${format(request.created_at, 'MMM d, yyyy')}`
                                : ''}
                        </p>
                    </div>
                    <div className="flex flex-wrap items-start gap-2 sm:justify-end">
                        <StatusBadge option={request.category} kind="neutral" />
                        <StatusBadge
                            option={request.priority}
                            kind="priority"
                        />
                        <StatusBadge option={request.status} />
                    </div>
                </div>
            ))}
        </div>
    );
}

function RequestsNeedingAttentionSkeleton() {
    return (
        <div className="grid gap-3">
            {[1, 2, 3].map((item) => (
                <div
                    key={item}
                    className="flex items-center justify-between gap-4"
                >
                    <div className="grid flex-1 gap-2">
                        <Skeleton className="h-4 w-48" />
                        <Skeleton className="h-3 w-36" />
                    </div>
                    <div className="flex gap-2">
                        <Skeleton className="h-6 w-20 rounded-full" />
                        <Skeleton className="h-6 w-20 rounded-full" />
                    </div>
                </div>
            ))}
        </div>
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
