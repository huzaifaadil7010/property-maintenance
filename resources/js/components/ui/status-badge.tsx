import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

type LabeledValue = { label: string; value: string };
type StatusBadgeKind = 'status' | 'priority' | 'unit' | 'neutral';

const toneClasses: Record<string, string> = {
    open: 'border-amber-200 bg-amber-50 text-amber-800',
    assigned: 'border-sky-200 bg-sky-50 text-sky-800',
    'in-progress': 'border-violet-200 bg-violet-50 text-violet-800',
    completed: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    closed: 'border-slate-200 bg-slate-100 text-slate-700',
    reopened: 'border-rose-200 bg-rose-50 text-rose-800',
    low: 'border-slate-200 bg-slate-50 text-slate-700',
    normal: 'border-sky-200 bg-sky-50 text-sky-800',
    high: 'border-orange-200 bg-orange-50 text-orange-800',
    urgent: 'border-rose-200 bg-rose-50 text-rose-800',
    vacant: 'border-sky-200 bg-sky-50 text-sky-800',
    occupied: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    'under-maintenance': 'border-amber-200 bg-amber-50 text-amber-800',
    available: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    unavailable: 'border-slate-200 bg-slate-100 text-slate-700',
};

const dotClasses: Record<string, string> = {
    open: 'bg-amber-500',
    assigned: 'bg-sky-500',
    'in-progress': 'bg-violet-500',
    completed: 'bg-emerald-500',
    closed: 'bg-slate-500',
    reopened: 'bg-rose-500',
    low: 'bg-slate-400',
    normal: 'bg-sky-500',
    high: 'bg-orange-500',
    urgent: 'bg-rose-500',
    vacant: 'bg-sky-500',
    occupied: 'bg-emerald-500',
    'under-maintenance': 'bg-amber-500',
    available: 'bg-emerald-500',
    unavailable: 'bg-slate-500',
};

export function StatusBadge({
    option,
    kind = 'status',
    className,
}: {
    option: LabeledValue;
    kind?: StatusBadgeKind;
    className?: string;
}) {
    const semantic = kind !== 'neutral' && toneClasses[option.value];

    return (
        <Badge
            variant="outline"
            className={cn(
                semantic
                    ? toneClasses[option.value]
                    : 'border-border bg-muted/70 text-muted-foreground',
                className,
            )}
        >
            <span
                aria-hidden="true"
                className={cn(
                    'size-1.5 rounded-full',
                    semantic
                        ? dotClasses[option.value]
                        : 'bg-muted-foreground/60',
                )}
            />
            {option.label}
        </Badge>
    );
}
