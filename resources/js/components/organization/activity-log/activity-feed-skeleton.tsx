import { Skeleton } from '@/components/ui/skeleton';

export function ActivityFeedSkeleton({ count = 4 }: { count?: number }) {
    return (
        <div className="grid gap-6" aria-hidden="true">
            {Array.from({ length: count }).map((_, index) => (
                <div key={index} className="flex gap-3">
                    <Skeleton className="size-10 shrink-0 rounded-full" />
                    <div className="grid flex-1 gap-2 pt-1">
                        <Skeleton className="h-4 w-2/5" />
                        <Skeleton className="h-3 w-full" />
                        <Skeleton className="h-3 w-20" />
                    </div>
                </div>
            ))}
        </div>
    );
}
