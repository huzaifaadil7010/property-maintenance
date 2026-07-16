import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

export function StatCardSkeleton() {
    return (
        <Card className="gap-4 py-5">
            <CardHeader className="flex-row items-center justify-between gap-4 px-5">
                <Skeleton className="h-4 w-24" />
                <Skeleton className="size-10 rounded-lg" />
            </CardHeader>
            <CardContent className="grid gap-2 px-5">
                <Skeleton className="h-9 w-16" />
                <Skeleton className="h-3 w-32" />
            </CardContent>
        </Card>
    );
}
