import { format } from 'date-fns';
import { ClipboardList } from 'lucide-react';
import { EmptyState } from '@/components/ui/empty-state';
import { StatusBadge } from '@/components/ui/status-badge';

type EnumOption = {
    label: string;
    value: string;
};

export type MaintenanceRequestNeedingAttention = {
    id: number;
    title: string;
    property: { id: number; name: string };
    unit: { id: number; name: string };
    category: EnumOption;
    priority: EnumOption;
    status: EnumOption;
    created_at: string | null;
};

type RequestsNeedingAttentionProps = {
    requests: MaintenanceRequestNeedingAttention[];
};

export function RequestsNeedingAttention({
    requests,
}: RequestsNeedingAttentionProps) {
    if (requests.length === 0) {
        return (
            <EmptyState
                icon={ClipboardList}
                title="No requests need attention"
                description="Open or reopened maintenance requests will appear here."
            />
        );
    }

    return (
        <div className="divide-y overflow-hidden rounded-2xl border border-border/90">
            {requests.map((request) => (
                <div
                    key={request.id}
                    className="grid gap-3 p-4 transition-colors hover:bg-accent/45 sm:grid-cols-[1fr_auto]"
                >
                    <div className="grid gap-1">
                        <p className="font-medium">{request.title}</p>
                        <p className="text-sm text-muted-foreground">
                            {request.property.name} · Unit {request.unit.name}
                            {request.created_at
                                ? ` · ${format(request.created_at, 'MMM d, yyyy')}`
                                : ''}
                        </p>
                    </div>
                    <div className="flex flex-wrap items-start gap-2 sm:justify-end">
                        <StatusBadge option={request.category} kind="neutral" />
                        <StatusBadge
                            option={request.priority}
                            kind="priority"
                        />
                        <StatusBadge option={request.status} />
                    </div>
                </div>
            ))}
        </div>
    );
}
