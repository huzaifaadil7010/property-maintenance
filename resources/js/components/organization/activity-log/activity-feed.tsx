import { formatDistanceToNow } from 'date-fns';
import {
    Activity as ActivityIcon,
    BadgeCheck,
    Building2,
    CircleCheckBig,
    ClipboardPlus,
    DoorOpen,
    Play,
    RefreshCw,
    RotateCcw,
    Trash2,
    UserRoundCog,
    UserRoundPlus,
    Wrench,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { cn } from '@/lib/utils';

export type ActivityLogItem = {
    id: number;
    event: string;
    title: string;
    description: string;
    icon_name: string;
    icon_color: string;
    icon_bg_color: string;
    created_at: string;
};

const activityIcons: Record<string, LucideIcon> = {
    Activity: ActivityIcon,
    BadgeCheck,
    Building2,
    CircleCheckBig,
    ClipboardPlus,
    DoorOpen,
    Play,
    RefreshCw,
    RotateCcw,
    Trash2,
    UserRoundCog,
    UserRoundPlus,
    Wrench,
};

export function ActivityFeed({
    activities,
}: {
    activities: ActivityLogItem[];
}) {
    return (
        <ol className="grid">
            {activities.map((activity, index) => {
                const Icon = activityIcons[activity.icon_name] ?? ActivityIcon;
                const isLast = index === activities.length - 1;

                return (
                    <li
                        key={activity.id}
                        className={cn('relative flex gap-3', !isLast && 'pb-6')}
                    >
                        <div className="relative flex shrink-0 flex-col items-center">
                            <div
                                className={cn(
                                    'relative z-10 flex size-10 items-center justify-center rounded-full ring-4 ring-card',
                                    activity.icon_bg_color,
                                    activity.icon_color,
                                )}
                            >
                                <Icon className="size-4.5" aria-hidden="true" />
                            </div>
                            {!isLast && (
                                <div className="absolute top-10 bottom-0 w-px bg-border" />
                            )}
                        </div>

                        <div className="min-w-0 flex-1 pt-0.5">
                            <p className="text-sm font-semibold text-foreground">
                                {activity.title}
                            </p>
                            <p className="mt-1 text-sm leading-5 text-muted-foreground">
                                {activity.description}
                            </p>
                            <time
                                className="mt-1.5 block text-xs text-muted-foreground/80"
                                dateTime={activity.created_at}
                            >
                                {formatDistanceToNow(
                                    new Date(activity.created_at),
                                    { addSuffix: true },
                                )}
                            </time>
                        </div>
                    </li>
                );
            })}
        </ol>
    );
}
