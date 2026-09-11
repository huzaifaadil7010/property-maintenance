import { Head, Link, usePage } from '@inertiajs/react';
import { Building2, CheckCircle2 } from 'lucide-react';
import { dashboard, login, register } from '@/wayfinder/routes';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Welcome" />
            <div className="relative flex min-h-dvh flex-col items-center overflow-hidden bg-background p-4 text-foreground lg:p-8">
                <div className="pointer-events-none absolute -top-48 right-[-10%] size-[32rem] rounded-full bg-primary/8 blur-3xl" />
                <header className="relative mb-6 w-full max-w-6xl shrink-0 text-sm">
                    <nav className="flex items-center justify-between gap-4">
                        <div className="flex items-center gap-2.5 font-semibold">
                            <span className="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-sm">
                                <Building2 className="size-4.5" />
                            </span>
                            Property Maintenance
                        </div>
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-flex h-10 items-center rounded-lg border border-input bg-card px-5 font-semibold shadow-xs transition-colors hover:border-primary/25 hover:bg-accent"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-flex h-10 items-center rounded-lg px-4 font-semibold transition-colors hover:bg-accent"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={register()}
                                    className="inline-flex h-10 items-center rounded-lg bg-primary px-5 font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                <div className="relative flex min-h-0 w-full max-w-6xl flex-1 items-center py-6 opacity-100 transition-opacity duration-700 starting:opacity-0">
                    <main className="grid w-full overflow-hidden rounded-[2rem] border border-primary/10 bg-card shadow-[0_28px_90px_rgba(20,45,32,0.1)] lg:grid-cols-[0.95fr_1.05fr]">
                        <section className="relative flex flex-col justify-center p-6 sm:p-8 lg:p-10">
                            <div className="absolute -top-24 -left-24 size-64 rounded-full bg-primary/10 blur-3xl" />
                            <div className="relative">
                                <div className="mb-5 flex w-fit items-center gap-2 rounded-full border border-primary/15 bg-primary/8 px-3 py-1.5 text-xs font-semibold text-primary">
                                    <Building2 className="size-3.5" />
                                    Property maintenance, organized
                                </div>

                                <h1 className="text-4xl leading-[1.08] font-semibold tracking-[-0.045em] text-foreground sm:text-5xl">
                                    From issue report to resolution, all in one
                                    place.
                                </h1>
                                <p className="mt-5 max-w-xl text-base leading-7 text-muted-foreground">
                                    Manage properties, units, residents, and
                                    maintenance work through one clear workflow.
                                </p>

                                <div className="mt-8 hidden gap-3 text-sm text-foreground/80 sm:grid">
                                    <div className="flex items-center gap-2.5">
                                        <CheckCircle2 className="size-4 text-primary" />
                                        Organization-wide portfolio visibility
                                    </div>
                                    <div className="flex items-center gap-2.5">
                                        <CheckCircle2 className="size-4 text-primary" />
                                        Simple request tracking for every role
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section className="flex min-h-0 items-center justify-center border-t border-primary/10 bg-primary/6 p-6 sm:p-10 lg:border-t-0 lg:border-l lg:p-14">
                            <div className="flex w-full items-center justify-center rounded-3xl border border-primary/10 bg-white p-6 shadow-[0_20px_50px_rgba(20,45,32,0.08)] sm:p-10">
                                <img
                                    src="/assets/property-houses.svg"
                                    alt="Diagram of managed residential properties"
                                    className="max-h-36 w-full object-contain sm:max-h-64"
                                />
                            </div>
                        </section>
                    </main>
                </div>
            </div>
        </>
    );
}
