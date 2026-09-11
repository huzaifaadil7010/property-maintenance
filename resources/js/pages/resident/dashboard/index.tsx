import { Deferred, Head, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import {
    ArrowRight,
    Building2,
    CircleAlert,
    CircleCheckBig,
    ClipboardList,
    Clock3,
    DoorOpen,
} from 'lucide-react';
import { StatCard } from '@/components/organization/dashboard/stat-card';
import { StatCardSkeleton } from '@/components/organization/dashboard/stat-card-skeleton';
import CreateMaintenanceRequestDialogue from '@/components/resident/maintenance-request/create-maintenance-request-dialogue';
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
import { dashboard, maintenanceRequests } from '@/wayfinder/routes/resident';

type EnumOption = { label: string; value: string };
type Residence = {
    property: { id: number; name: string };
    unit: { id: number; name: string };
};
type RecentMaintenanceRequest = {
    id: number;
    title: string;
    status: EnumOption;
    unit: { id: number; name: string };
    created_at: string | null;
};
type DashboardProps = {
    residence: { data: Residence } | null;
    maintenanceCategories?: EnumOption[];
    maintenancePriorities?: EnumOption[];
    recentMaintenanceRequests?: { data: RecentMaintenanceRequest[] };
    totalCompletedRequests?: number;
    totalInProgressRequests?: number;
    totalOpenRequests?: number;
};

const dashboardPropsToRefresh = [
    'recentMaintenanceRequests',
    'totalCompletedRequests',
    'totalInProgressRequests',
    'totalOpenRequests',
];

export default function Dashboard({
    residence,
    maintenanceCategories,
    maintenancePriorities,
    recentMaintenanceRequests,
    totalCompletedRequests,
    totalInProgressRequests,
    totalOpenRequests,
}: DashboardProps) {
    const residenceData = residence?.data;

    return (
        <>
            <Head title="Resident Dashboard" />
            <div className="page-container">
                <PageHeader
                    eyebrow="Your home"
                    title="Resident dashboard"
                    description="Everything you need to track your residence and maintenance activity."
                    actions={
                        residenceData && (
                            <Deferred
                                data={[
                                    'maintenanceCategories',
                                    'maintenancePriorities',
                                ]}
                                fallback={
                                    <Button
                                        className="w-full sm:w-auto"
                                        disabled
                                    >
                                        <Spinner />
                                        Report an issue
                                    </Button>
                                }
                            >
                                <CreateMaintenanceRequestDialogue
                                    categories={maintenanceCategories ?? []}
                                    priorities={maintenancePriorities ?? []}
                                    only={dashboardPropsToRefresh}
                                />
                            </Deferred>
                        )
                    }
                />
                {!residenceData ? (
                    <Card className="max-w-2xl border-dashed">
                        <CardHeader>
                            <CardTitle>No active residence</CardTitle>
                            <CardDescription>
                                You do not currently have an active unit
                                assigned. Contact your property manager for
                                help.
                            </CardDescription>
                        </CardHeader>
                    </Card>
                ) : (
                    <>
                        <Card className="relative overflow-hidden border-primary/15 bg-gradient-to-br from-white via-white to-primary/6">
                            <div className="absolute inset-y-0 left-0 w-1 bg-primary" />
                            <CardHeader className="flex-row items-center gap-4">
                                <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary ring-1 ring-primary/10">
                                    <Building2
                                        className="size-5"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div className="grid gap-1">
                                    <CardTitle>Your residence</CardTitle>
                                    <CardDescription>
                                        Your current property and unit.
                                    </CardDescription>
                                </div>
                            </CardHeader>
                            <CardContent className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-1">
                                    <span className="text-sm text-muted-foreground">
                                        Property
                                    </span>
                                    <span className="font-medium">
                                        {residenceData.property.name}
                                    </span>
                                </div>
                                <div className="grid gap-1">
                                    <span className="text-sm text-muted-foreground">
                                        Unit
                                    </span>
                                    <span className="flex items-center gap-2 font-medium">
                                        <DoorOpen
                                            className="size-4 text-muted-foreground"
                                            aria-hidden="true"
                                        />
                                        {residenceData.unit.name}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <Deferred
                                data="totalOpenRequests"
                                fallback={<StatCardSkeleton />}
                            >
                                <StatCard
                                    title="Open requests"
                                    value={totalOpenRequests ?? 0}
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
                                    value={totalInProgressRequests ?? 0}
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
                                    value={totalCompletedRequests ?? 0}
                                    description="Maintenance work completed"
                                    icon={CircleCheckBig}
                                    tone="emerald"
                                />
                            </Deferred>
                        </div>
                        <Card className="overflow-hidden">
                            <CardHeader className="flex-row items-center justify-between gap-4">
                                <div className="grid gap-1">
                                    <CardTitle>Recent requests</CardTitle>
                                    <CardDescription>
                                        Your five most recent maintenance
                                        requests.
                                    </CardDescription>
                                </div>
                                <Button variant="ghost" size="sm" asChild>
                                    <Link
                                        href={maintenanceRequests().url}
                                        prefetch
                                    >
                                        View all
                                        <ArrowRight aria-hidden="true" />
                                    </Link>
                                </Button>
                            </CardHeader>
                            <CardContent>
                                <Deferred
                                    data="recentMaintenanceRequests"
                                    fallback={<RecentRequestsSkeleton />}
                                >
                                    <RecentRequests
                                        requests={
                                            recentMaintenanceRequests?.data ??
                                            []
                                        }
                                    />
                                </Deferred>
                            </CardContent>
                        </Card>
                    </>
                )}
            </div>
        </>
    );
}

function RecentRequests({
    requests,
}: {
    requests: RecentMaintenanceRequest[];
}) {
    if (requests.length === 0) {
        return (
            <EmptyState
                icon={ClipboardList}
                title="No maintenance requests yet"
                description="Your recently reported issues will appear here."
            />
        );
    }

    return (
        <div className="divide-y overflow-hidden rounded-2xl border border-border/90">
            {requests.map((request) => (
                <div
                    key={request.id}
                    className="flex flex-col gap-3 p-4 transition-colors hover:bg-accent/45 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div className="grid gap-1">
                        <p className="font-medium">{request.title}</p>
                        <p className="text-sm text-muted-foreground">
                            Unit {request.unit.name}
                            {request.created_at
                                ? ` · ${format(request.created_at, 'MMM d, yyyy')}`
                                : ''}
                        </p>
                    </div>
                    <StatusBadge option={request.status} />
                </div>
            ))}
        </div>
    );
}

function RecentRequestsSkeleton() {
    return (
        <div className="grid gap-3">
            {[1, 2, 3].map((item) => (
                <div
                    key={item}
                    className="flex items-center justify-between gap-4"
                >
                    <div className="grid flex-1 gap-2">
                        <Skeleton className="h-4 w-40" />
                        <Skeleton className="h-3 w-28" />
                    </div>
                    <Skeleton className="h-6 w-20 rounded-full" />
                </div>
            ))}
        </div>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: dashboard().url }],
};
