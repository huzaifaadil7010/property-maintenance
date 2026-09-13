import { ShieldCheck } from 'lucide-react';
import { LandingAuthActions } from './landing-auth-actions';

const dashboardStats = [
    { label: 'Properties', value: '12' },
    { label: 'Units', value: '148', note: '141 occ' },
    { label: 'Technicians', value: '6', note: 'Active' },
    { label: 'Requests', value: '8', note: 'Open', attention: true },
];

const requests = [
    {
        title: 'Water leak under kitchen sink',
        location: 'Unit 3B • Oakwood Manor',
        priority: 'Urgent',
        status: 'In Progress',
        person: 'Marcus Vance (Plumbing)',
        initials: 'MV',
        note: 'SLA: 1h 45m rem',
        priorityClassName: 'bg-amber-100 text-amber-800',
        statusClassName: 'bg-cyan-100 text-cyan-800',
    },
    {
        title: 'HVAC thermostat not responding',
        location: 'Unit 12A • Highland Crest',
        priority: 'High',
        status: 'Assigned',
        person: 'Sarah Jenkins (HVAC)',
        initials: 'SJ',
        note: 'Dispatched 14:20',
        priorityClassName: 'bg-orange-100 text-orange-800',
        statusClassName: 'bg-indigo-100 text-indigo-800',
    },
    {
        title: 'Entry door latch sticking',
        location: 'Unit 104 • Elm Terrace',
        priority: 'Normal',
        status: 'Completed',
        person: 'Resident sign-off pending',
        initials: '✓',
        note: 'Photos logged (2)',
        priorityClassName: 'bg-slate-100 text-slate-700',
        statusClassName: 'bg-emerald-100 text-emerald-800',
    },
];

type LandingHeroProps = {
    authenticated: boolean;
};

export function LandingHero({ authenticated }: LandingHeroProps) {
    return (
        <section className="relative overflow-hidden px-4 pt-10 pb-16 md:px-8 md:pt-16 md:pb-24">
            <div className="pointer-events-none absolute -top-52 left-1/2 h-96 w-[68rem] -translate-x-1/2 rounded-full bg-primary/10 blur-3xl" />

            <div className="relative mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-12">
                <div className="flex flex-col items-start lg:col-span-6">
                    <div className="mb-6 inline-flex items-center gap-2 rounded-full bg-accent px-3 py-1.5">
                        <span className="size-2 rounded-full bg-primary" />
                        <span className="text-[11px] font-semibold tracking-[0.16em] text-accent-foreground uppercase">
                            Residential Property Operations
                        </span>
                    </div>

                    <h1 className="max-w-2xl text-4xl leading-[1.12] font-semibold tracking-[-0.04em] text-foreground sm:text-5xl lg:text-[3.5rem]">
                        One accountable workflow for residential maintenance
                        operations
                    </h1>
                    <p className="mt-6 max-w-xl text-base leading-7 text-muted-foreground sm:text-lg">
                        Connect owners, residents, and technicians from first
                        report to confirmed resolution. Eliminate phone tag,
                        messy spreadsheets, and lost requests.
                    </p>

                    <LandingAuthActions
                        authenticated={authenticated}
                        className="mt-8"
                    />

                    <div className="mt-6 inline-flex items-center gap-2 rounded-full bg-secondary px-3.5 py-2 text-xs font-medium text-secondary-foreground">
                        <ShieldCheck className="size-4 text-primary" />
                        Built for small &amp; growing residential portfolios
                    </div>
                </div>

                <DashboardPreview />
            </div>
        </section>
    );
}

function DashboardPreview() {
    return (
        <div className="w-full lg:col-span-6">
            <div className="rounded-2xl border border-border/70 bg-card p-4 shadow-[0_20px_55px_rgba(20,45,32,0.14)] sm:p-6">
                <div className="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-border pb-4">
                    <div className="flex items-center gap-2.5">
                        <span className="size-2.5 animate-pulse rounded-full bg-primary" />
                        <span className="font-semibold">Ops Command</span>
                        <span className="rounded bg-accent px-2 py-0.5 text-[11px] font-semibold text-accent-foreground">
                            Live Dispatch
                        </span>
                    </div>
                    <span className="text-xs text-muted-foreground">
                        Synchronized 1m ago
                    </span>
                </div>

                <div className="mb-5 grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                    {dashboardStats.map((stat) => (
                        <div
                            key={stat.label}
                            className="rounded-xl bg-secondary p-3 transition-transform duration-200 hover:scale-[1.01] motion-reduce:transform-none"
                        >
                            <span className="block text-[11px] font-medium text-muted-foreground">
                                {stat.label}
                            </span>
                            <div className="mt-1 flex items-baseline gap-1.5">
                                <span
                                    className={
                                        stat.attention
                                            ? 'text-lg font-semibold text-orange-700'
                                            : 'text-lg font-semibold'
                                    }
                                >
                                    {stat.value}
                                </span>
                                {stat.note && (
                                    <span className="text-[10px] font-semibold text-primary">
                                        {stat.note}
                                    </span>
                                )}
                            </div>
                        </div>
                    ))}
                </div>

                <div className="grid gap-3">
                    {requests.map((request) => (
                        <article
                            key={request.title}
                            className="rounded-xl border border-border/60 bg-card p-3.5 shadow-xs transition-shadow hover:shadow-md"
                        >
                            <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                <div>
                                    <h2 className="text-sm font-semibold">
                                        {request.title}
                                    </h2>
                                    <p className="text-xs text-muted-foreground">
                                        {request.location}
                                    </p>
                                </div>
                                <div className="flex shrink-0 items-center gap-1.5">
                                    <span
                                        className={`rounded-full px-2 py-0.5 text-[10px] font-semibold ${request.priorityClassName}`}
                                    >
                                        {request.priority}
                                    </span>
                                    <span
                                        className={`rounded-full px-2 py-0.5 text-[10px] font-semibold ${request.statusClassName}`}
                                    >
                                        {request.status}
                                    </span>
                                </div>
                            </div>
                            <div className="mt-2 flex flex-col justify-between gap-2 border-t border-border/70 pt-2 text-xs sm:flex-row sm:items-center">
                                <div className="flex items-center gap-2">
                                    <span className="flex size-5 items-center justify-center rounded-full bg-accent text-[9px] font-semibold text-accent-foreground">
                                        {request.initials}
                                    </span>
                                    <span>{request.person}</span>
                                </div>
                                <span className="font-medium text-muted-foreground">
                                    {request.note}
                                </span>
                            </div>
                        </article>
                    ))}
                </div>
            </div>
        </div>
    );
}
