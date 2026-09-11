import type { LucideIcon } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';

type StatCardProps = {
    title: string;
    value: number;
    description: string;
    icon: LucideIcon;
    tone?: 'brand' | 'amber' | 'sky' | 'violet' | 'emerald';
};

const toneClasses = {
    brand: 'bg-primary/10 text-primary ring-primary/10',
    amber: 'bg-amber-50 text-amber-700 ring-amber-100',
    sky: 'bg-sky-50 text-sky-700 ring-sky-100',
    violet: 'bg-violet-50 text-violet-700 ring-violet-100',
    emerald: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
};

export function StatCard({
    title,
    value,
    description,
    icon: Icon,
    tone = 'brand',
}: StatCardProps) {
    return (
        <Card className="group relative gap-4 overflow-hidden py-5 transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-px hover:border-primary/20 hover:shadow-[0_12px_34px_rgba(20,45,32,0.08)] motion-reduce:transform-none">
            <div className="absolute inset-x-5 top-0 h-px bg-gradient-to-r from-transparent via-primary/25 to-transparent" />
            <CardHeader className="flex-row items-center justify-between gap-4 px-5">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
                <div
                    className={cn(
                        'flex size-10 shrink-0 items-center justify-center rounded-xl ring-1 transition-transform duration-200 group-hover:scale-105 motion-reduce:transform-none',
                        toneClasses[tone],
                    )}
                >
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
