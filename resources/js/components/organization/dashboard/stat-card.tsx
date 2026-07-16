import type { LucideIcon } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type StatCardProps = {
    title: string;
    value: number;
    description: string;
    icon: LucideIcon;
};

export function StatCard({
    title,
    value,
    description,
    icon: Icon,
}: StatCardProps) {
    return (
        <Card className="gap-4 overflow-hidden py-5">
            <CardHeader className="flex-row items-center justify-between gap-4 px-5">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
                <div className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <Icon className="size-5" aria-hidden="true" />
                </div>
            </CardHeader>
            <CardContent className="grid gap-1 px-5">
                <p className="text-3xl font-semibold tracking-tight tabular-nums">
                    {value.toLocaleString()}
                </p>
                <p className="text-xs text-muted-foreground">{description}</p>
            </CardContent>
        </Card>
    );
}
