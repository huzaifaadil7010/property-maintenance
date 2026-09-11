import { Link } from '@inertiajs/react';
import { Building2, CheckCircle2, ShieldCheck, Wrench } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import type { AuthLayoutProps } from '@/types';
import { home } from '@/wayfinder/routes';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="relative grid min-h-svh overflow-hidden bg-background lg:grid-cols-[1.05fr_0.95fr]">
            <section className="relative hidden overflow-hidden border-r border-primary/10 bg-[#173d30] p-10 text-white lg:flex lg:flex-col lg:justify-between xl:p-14">
                <div className="absolute -top-32 -left-24 size-96 rounded-full bg-emerald-300/12 blur-3xl" />
                <div className="absolute -right-28 -bottom-36 size-[28rem] rounded-full bg-lime-200/10 blur-3xl" />
                <Link
                    href={home()}
                    className="relative flex items-center gap-3 font-semibold"
                >
                    <span className="flex size-10 items-center justify-center rounded-xl bg-white/12 ring-1 ring-white/15">
                        <Building2 className="size-5" />
                    </span>
                    Property Maintenance
                </Link>

                <div className="relative max-w-xl">
                    <p className="text-xs font-semibold tracking-[0.18em] text-emerald-200 uppercase">
                        One connected workspace
                    </p>
                    <h2 className="mt-4 text-4xl leading-[1.12] font-semibold tracking-[-0.04em] xl:text-5xl">
                        Every property issue, clearly managed.
                    </h2>
                    <p className="mt-5 max-w-lg text-base leading-7 text-emerald-50/70">
                        Keep owners, residents, and technicians aligned from the
                        first report through resolution.
                    </p>
                    <div className="mt-8 grid gap-3 text-sm text-emerald-50/85">
                        <div className="flex items-center gap-3">
                            <CheckCircle2 className="size-4 text-emerald-300" />{' '}
                            Clear maintenance progress
                        </div>
                        <div className="flex items-center gap-3">
                            <Wrench className="size-4 text-emerald-300" />{' '}
                            Role-focused workspaces
                        </div>
                        <div className="flex items-center gap-3">
                            <ShieldCheck className="size-4 text-emerald-300" />{' '}
                            Secure account access
                        </div>
                    </div>
                </div>

                <p className="relative text-xs text-emerald-100/50">
                    Property operations, made calm and accountable.
                </p>
            </section>

            <section className="flex min-h-svh items-center justify-center p-5 sm:p-8 lg:p-12">
                <div className="w-full max-w-md rounded-3xl border border-border/90 bg-card p-6 shadow-[0_20px_60px_rgba(20,45,32,0.08)] sm:p-9">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4 lg:items-start">
                            <Link
                                href={home()}
                                className="flex items-center gap-2 font-medium lg:hidden"
                            >
                                <div className="flex size-10 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-sm">
                                    <AppLogoIcon className="size-5" />
                                </div>
                                <span className="font-semibold">
                                    Property Maintenance
                                </span>
                            </Link>

                            <div className="space-y-2 text-center lg:text-left">
                                <h1 className="text-2xl font-semibold tracking-[-0.025em]">
                                    {title}
                                </h1>
                                <p className="text-sm leading-6 text-muted-foreground">
                                    {description}
                                </p>
                            </div>
                        </div>
                        {children}
                    </div>
                </div>
            </section>
        </div>
    );
}
