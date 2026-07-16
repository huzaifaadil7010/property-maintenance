import { Head, Link, usePage } from '@inertiajs/react';
import { Building2, CheckCircle2 } from 'lucide-react';
import { dashboard, login, register } from '@/wayfinder/routes';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Welcome" />
            <div className="flex h-dvh flex-col items-center overflow-hidden bg-[#FDFDFC] p-4 text-[#1b1b18] lg:p-8 dark:bg-[#0a0a0a]">
                <header className="mb-4 w-full max-w-[335px] shrink-0 text-sm not-has-[nav]:hidden lg:max-w-4xl">
                    <nav className="flex items-center justify-end gap-4">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={register()}
                                    className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                <div className="flex min-h-0 w-full max-w-[335px] flex-1 items-center opacity-100 transition-opacity duration-700 lg:max-w-4xl starting:opacity-0">
                    <main className="grid max-h-[560px] w-full overflow-hidden rounded-3xl border border-black/10 bg-white shadow-2xl shadow-black/5 lg:grid-cols-[0.9fr_1.1fr] dark:border-white/10 dark:bg-[#151515] dark:shadow-black/30">
                        <section className="relative flex flex-col justify-center p-6 sm:p-8 lg:p-10">
                            <div className="absolute -top-24 -left-24 size-64 rounded-full bg-emerald-200/40 blur-3xl dark:bg-emerald-500/10" />
                            <div className="relative">
                                <div className="mb-4 flex w-fit items-center gap-2 rounded-full border border-emerald-600/15 bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-800 dark:border-emerald-400/20 dark:text-emerald-300">
                                    <Building2 className="size-3.5" />
                                    Property maintenance, organized
                                </div>

                                <h1 className="text-3xl leading-tight font-semibold tracking-[-0.035em] text-zinc-950 sm:text-4xl dark:text-white">
                                    From issue report to resolution, all in one
                                    place.
                                </h1>
                                <p className="mt-4 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                    Manage properties, units, residents, and
                                    maintenance work through one clear workflow.
                                </p>

                                <div className="mt-6 hidden gap-3 text-xs text-zinc-700 sm:grid dark:text-zinc-300">
                                    <div className="flex items-center gap-2.5">
                                        <CheckCircle2 className="size-4 text-emerald-600 dark:text-emerald-400" />
                                        Organization-wide portfolio visibility
                                    </div>
                                    <div className="flex items-center gap-2.5">
                                        <CheckCircle2 className="size-4 text-emerald-600 dark:text-emerald-400" />
                                        Simple request tracking for every role
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section className="flex min-h-0 items-center justify-center border-t border-black/10 bg-emerald-50 p-6 sm:p-8 lg:border-t-0 lg:border-l lg:p-10 dark:border-white/10 dark:bg-emerald-950/20">
                            <div className="flex w-full items-center justify-center rounded-2xl border border-black/5 bg-white p-6 shadow-sm sm:p-8">
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
