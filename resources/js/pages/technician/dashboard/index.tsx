import { Deferred, Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    CircleAlert,
    CircleCheckBig,
    ClipboardList,
    Clock3,
} from 'lucide-react';
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
import { StatusBadge } from '@/components/ui/status-badge';
import { dashboard, maintenanceRequests } from '@/wayfinder/routes/technician';

type EnumOption = { label: string; value: string };
type ActiveJob = {
    id: number;
    title: string;
    property: { id: number; name: string };
    unit: { id: number; name: string };
    category: EnumOption;
    priority: EnumOption;
    status: EnumOption;
};
type DashboardProps = {
    currentActiveJobs?: { data: ActiveJob[] };
    totalAssignedJobs?: number;
    totalCompletedJobs?: number;
    totalInProgressJobs?: number;
};

export default function Dashboard({
    currentActiveJobs,
    totalAssignedJobs,
    totalCompletedJobs,
    totalInProgressJobs,
}: DashboardProps) {
    return (
        <>
            <Head title="Technician Dashboard" />
            <div className="page-container">
                <PageHeader
                    eyebrow="Your workday"
                    title="Technician dashboard"
                    description="View your assigned maintenance work and keep every job moving."
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Deferred
                        data="totalAssignedJobs"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="Assigned jobs"
                            value={totalAssignedJobs ?? 0}
                            description="Ready for you to begin"
                            icon={CircleAlert}
                            tone="amber"
                        />
                    </Deferred>
                    <Deferred
                        data="totalInProgressJobs"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="In-progress jobs"
                            value={totalInProgressJobs ?? 0}
                            description="Maintenance currently underway"
                            icon={Clock3}
                            tone="violet"
                        />
                    </Deferred>
                    <Deferred
                        data="totalCompletedJobs"
                        fallback={<StatCardSkeleton />}
                    >
                        <StatCard
                            title="Completed jobs"
                            value={totalCompletedJobs ?? 0}
                            description="Maintenance work completed"
                            icon={CircleCheckBig}
                            tone="emerald"
                        />
                    </Deferred>
                </div>

                <Card className="overflow-hidden">
                    <CardHeader className="flex-row items-center justify-between gap-4">
                        <div className="grid gap-1">
                            <CardTitle>Current active jobs</CardTitle>
                            <CardDescription>
                                Your five newest assigned or in-progress jobs.
                            </CardDescription>
                        </div>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={maintenanceRequests().url} prefetch>
                                View all
                                <ArrowRight aria-hidden="true" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <Deferred
                            data="currentActiveJobs"
                            fallback={<ActiveJobsSkeleton />}
                        >
                            <ActiveJobs jobs={currentActiveJobs?.data ?? []} />
                        </Deferred>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

function ActiveJobs({ jobs }: { jobs: ActiveJob[] }) {
    if (jobs.length === 0) {
        return (
            <EmptyState
                icon={ClipboardList}
                title="No active jobs"
                description="Newly assigned maintenance work will appear here."
            />
        );
    }

    return (
        <div className="divide-y overflow-hidden rounded-2xl border border-border/90">
            {jobs.map((job) => (
                <div
                    key={job.id}
                    className="grid gap-3 p-4 transition-colors hover:bg-accent/45 sm:grid-cols-[1fr_auto]"
                >
                    <div className="grid gap-1">
                        <p className="font-medium">{job.title}</p>
                        <p className="text-sm text-muted-foreground">
                            {job.property.name} · Unit {job.unit.name}
                        </p>
                    </div>
                    <div className="flex flex-wrap items-start gap-2 sm:justify-end">
                        <StatusBadge option={job.category} kind="neutral" />
                        <StatusBadge option={job.priority} kind="priority" />
                        <StatusBadge option={job.status} />
                    </div>
                </div>
            ))}
        </div>
    );
}

function ActiveJobsSkeleton() {
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
    breadcrumbs: [{ title: 'Dashboard', href: dashboard().url }],
};
