import type { ReactNode } from 'react';

type PageHeaderProps = {
    title: string;
    description?: string;
    eyebrow?: string;
    actions?: ReactNode;
};

export function PageHeader({
    title,
    description,
    eyebrow,
    actions,
}: PageHeaderProps) {
    return (
        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div className="grid gap-1.5">
                {eyebrow && (
                    <p className="text-xs font-semibold tracking-[0.16em] text-primary uppercase">
                        {eyebrow}
                    </p>
                )}
                <h1 className="text-2xl font-semibold tracking-[-0.025em] text-foreground md:text-[1.75rem]">
                    {title}
                </h1>
                {description && (
                    <p className="max-w-2xl text-sm leading-6 text-muted-foreground">
                        {description}
                    </p>
                )}
            </div>
            {actions && <div className="shrink-0">{actions}</div>}
        </div>
    );
}
