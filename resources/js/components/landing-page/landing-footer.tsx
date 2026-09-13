import { Building2 } from 'lucide-react';

const navigation = [
    { label: 'Features', href: '#features' },
    { label: 'Workflow', href: '#workflow' },
    { label: 'Roles', href: '#roles' },
    { label: 'Pricing', href: '#pricing' },
];

export function LandingFooter() {
    return (
        <footer className="border-t border-border/50 bg-secondary px-4 py-8 shadow-[0_-1px_6px_rgba(20,45,32,0.03)] md:px-8">
            <div className="mx-auto flex w-full max-w-[1600px] flex-col items-center justify-between gap-6 lg:flex-row">
                <div className="flex items-center gap-4">
                    <span className="flex size-7 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <Building2 className="size-4" />
                    </span>
                    <span className="text-xs text-muted-foreground">
                        © 2024 Property Maintenance SaaS Inc. All rights
                        reserved.
                    </span>
                </div>

                <nav
                    aria-label="Footer navigation"
                    className="flex flex-wrap items-center justify-center gap-x-6 gap-y-3"
                >
                    {navigation.map((item) => (
                        <a
                            key={item.href}
                            href={item.href}
                            className="text-xs text-muted-foreground transition-colors hover:text-foreground"
                        >
                            {item.label}
                        </a>
                    ))}
                    <span className="text-xs text-muted-foreground">
                        Privacy
                    </span>
                    <span className="text-xs text-muted-foreground">Terms</span>
                </nav>

                <div className="flex items-center gap-2 rounded-full bg-accent px-3 py-1.5">
                    <span className="size-2 animate-pulse rounded-full bg-primary" />
                    <span className="text-[10px] font-medium tracking-wider text-primary uppercase">
                        Systems Operational
                    </span>
                </div>
            </div>
        </footer>
    );
}
