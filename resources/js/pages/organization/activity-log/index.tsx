import {
    Head,
    InfiniteScroll,
    setLayoutProps,
    usePage,
} from '@inertiajs/react';
import { Activity, LoaderCircle } from 'lucide-react';
import { ActivityFeed } from '@/components/organization/activity-log/activity-feed';
import type { ActivityLogItem } from '@/components/organization/activity-log/activity-feed';
import { EmptyState } from '@/components/ui/empty-state';
import { PageHeader } from '@/components/ui/page-header';
import { index as activityLogsIndex } from '@/wayfinder/App/Http/Controllers/Organization/ActivityLogsController';
import { dashboard } from '@/wayfinder/routes/organization';

type ActivityLogsPageProps = {
    activityLogs: {
        data: ActivityLogItem[];
    };
};

export default function Index({ activityLogs }: ActivityLogsPageProps) {
    const { currentOrganization } = usePage().props;

    setLayoutProps({
        breadcrumbs: currentOrganization
            ? [
                  {
                      title: 'Dashboard',
                      href: dashboard(currentOrganization.uuid).url,
                  },
                  {
                      title: 'Activity logs',
                      href: activityLogsIndex(currentOrganization.uuid).url,
                  },
              ]
            : [],
    });

    return (
        <>
            <Head title="Activity logs" />

            <div className="page-container">
                <PageHeader
                    eyebrow="Organization history"
                    title="Activity logs"
                    description="Follow the latest changes made across your organization."
                />

                <section className="surface-card p-5 sm:p-6">
                    {activityLogs.data.length === 0 ? (
                        <EmptyState
                            icon={Activity}
                            title="No activity yet"
                            description="New organization activity will appear here as your team works."
                        />
                    ) : (
                        <InfiniteScroll
                            data="activityLogs"
                            onlyNext
                            preserveUrl={false}
                            next={({ hasMore, loading }) =>
                                loading ? (
                                    <div className="flex items-center justify-center gap-2 py-6 text-sm text-muted-foreground">
                                        <LoaderCircle className="size-4 animate-spin" />
                                        Loading more activity
                                    </div>
                                ) : !hasMore ? (
                                    <p className="mt-6 border-t border-border pt-5 text-center text-sm text-muted-foreground">
                                        You have reached the end of the activity
                                        log.
                                    </p>
                                ) : null
                            }
                        >
                            <ActivityFeed activities={activityLogs.data} />
                        </InfiniteScroll>
                    )}
                </section>
            </div>
        </>
    );
}
