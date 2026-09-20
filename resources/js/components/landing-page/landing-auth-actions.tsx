import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import BillingController from '@/wayfinder/App/Http/Controllers/Settings/BillingController';
import { dashboard, login, register } from '@/wayfinder/routes';

type LandingAuthActionsProps = {
    authenticated: boolean;
    placement?: 'header' | 'hero' | 'pricing' | 'mobile';
    className?: string;
    planSlug?: string;
    canManageBilling?: boolean;
};

export function LandingAuthActions({
    authenticated,
    placement = 'hero',
    className,
    planSlug,
    canManageBilling = false,
}: LandingAuthActionsProps) {
    const isHeader = placement === 'header';
    const isMobile = placement === 'mobile';
    const isPricing = placement === 'pricing';
    const primaryClassName = cn(
        'shadow-sm',
        isHeader && 'h-10 px-5',
        placement === 'hero' && 'h-12 px-6',
        isPricing && 'h-12 w-full',
        isMobile && 'h-11 w-full',
    );

    if (authenticated) {
        if (isPricing && planSlug) {
            return (
                <div className={cn('w-full', className)}>
                    <Button
                        asChild={canManageBilling}
                        disabled={!canManageBilling}
                        className={primaryClassName}
                    >
                        {canManageBilling ? (
                            <Link
                                href={BillingController.index({
                                    query: { plan: planSlug },
                                })}
                            >
                                Choose this plan
                            </Link>
                        ) : (
                            <span>Owner access required</span>
                        )}
                    </Button>
                </div>
            );
        }

        return (
            <div
                className={cn(isPricing || isMobile ? 'w-full' : '', className)}
            >
                <Button asChild className={primaryClassName}>
                    <Link href={dashboard()}>
                        Dashboard
                        {!isHeader && !isMobile && <ArrowRight />}
                    </Link>
                </Button>
            </div>
        );
    }

    if (isPricing) {
        return (
            <div className={cn('w-full', className)}>
                <Button asChild className={primaryClassName}>
                    <Link href={register({ query: { plan: planSlug } })}>
                        Start 14-Day Full Access Trial
                    </Link>
                </Button>
            </div>
        );
    }

    if (isMobile) {
        return (
            <div className={cn('grid w-full gap-3', className)}>
                <Button asChild variant="outline" className="h-11 w-full">
                    <Link href={login()}>Log In</Link>
                </Button>
                <Button asChild className={primaryClassName}>
                    <Link href={register()}>Create Account</Link>
                </Button>
            </div>
        );
    }

    if (isHeader) {
        return (
            <div className={cn('flex items-center gap-2', className)}>
                <Button asChild variant="ghost" className="h-10 px-4">
                    <Link href={login()}>Log In</Link>
                </Button>
                <Button asChild className={primaryClassName}>
                    <Link href={register()}>Create Account</Link>
                </Button>
            </div>
        );
    }

    return (
        <div className={cn('flex flex-wrap items-center gap-4', className)}>
            <Button asChild className={primaryClassName}>
                <Link href={register()}>
                    Create Account
                    <ArrowRight />
                </Link>
            </Button>
            <Button asChild variant="outline" className="h-12 bg-card px-6">
                <Link href={login()}>Log In</Link>
            </Button>
        </div>
    );
}
