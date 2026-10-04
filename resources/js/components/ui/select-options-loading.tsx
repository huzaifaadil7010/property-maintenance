import { Spinner } from '@/components/ui/spinner';

export function SelectOptionsLoading({ label }: { label: string }) {
    return (
        <p
            role="status"
            className="flex items-center gap-2 text-xs text-muted-foreground"
        >
            <Spinner
                className="size-3"
                role="presentation"
                aria-hidden="true"
            />
            {label}
        </p>
    );
}
