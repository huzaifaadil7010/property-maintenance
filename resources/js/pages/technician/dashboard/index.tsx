import { Deferred, Head, Link } from '@inertiajs/react';
import { ArrowRight, CircleAlert, CircleCheckBig, Clock3 } from 'lucide-react';
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
import { Skeleton } from '@/components/ui/skeleton';
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
            <div className="flex min-h-0 flex-1 p-4 md:p-6 lg:p-8">
                <div className="max-w-10xl mx-auto flex w-full flex-1 flex-col gap-6">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Technician dashboard
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            View your assigned maintenance work and progress.
                        </p>
                    </div>

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
                            />
                        </Deferred>
                    </div>

                    <Card>
                        <CardHeader className="flex-row items-center justify-between gap-4">
                            <div className="grid gap-1">
                                <CardTitle>Current active jobs</CardTitle>
                                <CardDescription>
                                    Your five newest assigned or in-progress
                                    jobs.
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
                                <ActiveJobs
                                    jobs={currentActiveJobs?.data ?? []}
                                />
                            </Deferred>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

function ActiveJobs({ jobs }: { jobs: ActiveJob[] }) {
    if (jobs.length === 0) {
        return (
            <p className="py-4 text-sm text-muted-foreground">
                You do not have any active jobs right now.
            </p>
        );
    }

    return (
        <div className="divide-y rounded-lg border">
            {jobs.map((job) => (
                <div
                    key={job.id}
                    className="grid gap-3 p-4 sm:grid-cols-[1fr_auto]"
                >
                    <div className="grid gap-1">
                        <p className="font-medium">{job.title}</p>
                        <p className="text-sm text-muted-foreground">
                            {job.property.name} · Unit {job.unit.name}
                        </p>
                    </div>
                    <div className="flex flex-wrap items-start gap-2 sm:justify-end">
                        <span className="rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">
                            {job.category.label}
                        </span>
                        <span className="rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">
                            {job.priority.label}
                        </span>
                        <span className="rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">
                            {job.status.label}
                        </span>
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
