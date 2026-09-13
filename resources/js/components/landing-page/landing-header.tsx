import { Building2, Menu } from 'lucide-react';
import type { MouseEvent } from 'react';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { LandingAuthActions } from './landing-auth-actions';

const navigation = [
    { label: 'Features', href: '#features' },
    { label: 'Workflow', href: '#workflow' },
    { label: 'Roles', href: '#roles' },
    { label: 'Pricing', href: '#pricing' },
];

type LandingHeaderProps = {
    authenticated: boolean;
};

export function LandingHeader({ authenticated }: LandingHeaderProps) {
    const handleNavigation = (
        event: MouseEvent<HTMLAnchorElement>,
        href: string,
    ) => {
        const section = document.querySelector<HTMLElement>(href);

        if (!section) {
            return;
        }

        event.preventDefault();
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.history.pushState(null, '', href);
    };

    return (
        <header className="fixed inset-x-0 top-0 z-50 border-b border-border/50 bg-background/90 shadow-[0_1px_8px_rgba(20,45,32,0.04)] backdrop-blur-xl">
            <div className="mx-auto flex h-20 w-full max-w-[1600px] items-center justify-between px-4 md:px-8">
                <div className="flex items-center gap-12">
                    <a
                        href="#top"
                        onClick={(event) => handleNavigation(event, '#top')}
                        className="flex items-center gap-3 font-semibold tracking-tight"
                    >
                        <span className="flex size-10 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-sm">
                            <Building2 className="size-5" />
                        </span>
                        <span className="hidden text-lg sm:inline">
                            Property Maintenance
                        </span>
                    </a>

                    <nav
                        aria-label="Primary navigation"
                        className="hidden items-center gap-8 lg:flex"
                    >
                        {navigation.map((item) => (
                            <a
                                key={item.href}
                                href={item.href}
                                onClick={(event) =>
                                    handleNavigation(event, item.href)
                                }
                                className="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus-visible:text-foreground focus-visible:outline-none"
                            >
                                {item.label}
                            </a>
                        ))}
                    </nav>
                </div>

                <div className="hidden lg:block">
                    <LandingAuthActions
                        authenticated={authenticated}
                        placement="header"
                    />
                </div>

                <Sheet>
                    <SheetTrigger asChild>
                        <Button
                            variant="outline"
                            size="icon"
                            className="lg:hidden"
                            aria-label="Open navigation menu"
                        >
                            <Menu />
                        </Button>
                    </SheetTrigger>
                    <SheetContent className="w-[min(22rem,88vw)] p-6">
                        <SheetHeader className="p-0 text-left">
                            <SheetTitle className="flex items-center gap-3">
                                <span className="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                                    <Building2 className="size-4" />
                                </span>
                                Property Maintenance
                            </SheetTitle>
                        </SheetHeader>

                        <nav
                            aria-label="Mobile navigation"
                            className="grid gap-2 pt-6"
                        >
                            {navigation.map((item) => (
                                <SheetClose asChild key={item.href}>
                                    <a
                                        href={item.href}
                                        onClick={(event) =>
                                            handleNavigation(event, item.href)
                                        }
                                        className="rounded-lg px-3 py-3 text-sm font-semibold text-foreground transition-colors hover:bg-accent"
                                    >
                                        {item.label}
                                    </a>
                                </SheetClose>
                            ))}
                        </nav>

                        <div className="mt-auto border-t border-border pt-6">
                            <LandingAuthActions
                                authenticated={authenticated}
                                placement="mobile"
                            />
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </header>
    );
}
