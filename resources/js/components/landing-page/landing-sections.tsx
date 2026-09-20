import type { LucideIcon } from 'lucide-react';
import {
    BadgeCheck,
    BadgeDollarSign,
    Bolt,
    Building2,
    Camera,
    Check,
    CheckCircle2,
    ChevronRight,
    CircleX,
    Clock3,
    Handshake,
    History,
    House,
    ImageOff,
    LayoutDashboard,
    Send,
    Star,
    ThumbsUp,
    Timer,
    Wrench,
} from 'lucide-react';
import type { Plan } from '@/types';
import { LandingAuthActions } from './landing-auth-actions';

const capabilities: Array<{
    icon: LucideIcon;
    title: string;
    description: string;
    label: string;
}> = [
    {
        icon: Building2,
        title: 'Portfolio & Unit Management',
        description:
            'Centralize building rosters, unit floorplans, active tenant directories, and historical maintenance track records under unified property nodes.',
        label: 'Occupancy & Unit Ledger',
    },
    {
        icon: Camera,
        title: 'Issue Submission with Photos',
        description:
            'Tenants submit requests with compulsory image uploads, category tagging, and severity markers to ensure diagnostic clarity prior to dispatch.',
        label: 'Zero-Diagnostic-Doubt Flow',
    },
    {
        icon: Wrench,
        title: 'Direct Technician Routing',
        description:
            'Route tasks automatically based on trade certifications (HVAC, plumbing, electrical) and internal vs. external vendor availability.',
        label: 'Trade Routing Protocols',
    },
    {
        icon: Clock3,
        title: 'Real-Time Work Progress',
        description:
            'Field techs log time intervals, internal notes, delay reasons, and ordering of parts straight from handheld interfaces while on-premise.',
        label: 'Field Activity Feed',
    },
    {
        icon: BadgeDollarSign,
        title: 'Verified Completion & Costs',
        description:
            'Collect mandatory resolution images, itemized hardware costs, and contractor labor rates to assemble instant auditable expense records.',
        label: 'Verified Proof Standard',
    },
    {
        icon: History,
        title: 'Immutable Audit History',
        description:
            'Every status toggle, text exchange, notification dispatch, and photo upload receives a cryptographic timestamp for complete compliance protection.',
        label: 'Audit Ledger Retention',
    },
];

const workflow: Array<{
    title: string;
    description: string;
    label: string;
    icon: LucideIcon;
}> = [
    {
        title: 'Report',
        description:
            'Resident logs issue with mandatory diagnostic photos and urgency flags.',
        label: 'Mobile Intake',
        icon: Camera,
    },
    {
        title: 'Assign',
        description:
            'Owner selects specialist according to skill match and job availability.',
        label: 'Instant Dispatch',
        icon: Send,
    },
    {
        title: 'Start Work',
        description:
            'Technician checks in on-site, activating timer and tenant notifications.',
        label: 'SLA Timer Active',
        icon: Timer,
    },
    {
        title: 'Complete',
        description:
            'Tech logs resolution evidence, labor hours, and component receipts.',
        label: 'Photo Resolution',
        icon: BadgeCheck,
    },
    {
        title: 'Confirm',
        description:
            'Resident inspects completed repair and provides decisive closing sign-off.',
        label: 'Final Sign-off',
        icon: ThumbsUp,
    },
];

const roles: Array<{
    eyebrow: string;
    title: string;
    icon: LucideIcon;
    points: Array<{ title: string; description: string }>;
    metricLabel: string;
    metric: string;
    metricSuffix: string;
}> = [
    {
        eyebrow: 'Control & Capital',
        title: 'Property Owner',
        icon: LayoutDashboard,
        points: [
            {
                title: 'Portfolio Visibility:',
                description:
                    'Real-time macro oversight across all properties, units, and active maintenance contracts.',
            },
            {
                title: 'Assignment Control:',
                description:
                    'Approve contractor estimates, review trade schedules, and balance operational spend.',
            },
            {
                title: 'Expense Ledger:',
                description:
                    'Instant aggregation of maintenance line items for simplified annual tax preparation.',
            },
        ],
        metricLabel: 'Key Metric:',
        metric: '100% cost transparency',
        metricSuffix: ' per unit & building.',
    },
    {
        eyebrow: 'Peace of Mind',
        title: 'Resident Tenant',
        icon: House,
        points: [
            {
                title: 'Effortless Submissions:',
                description:
                    'Two-minute mobile intake workflow with intuitive guidance and photo attachments.',
            },
            {
                title: 'Real-Time Tracking:',
                description:
                    'Know the exact moment a technician is scheduled, dispatched, or arriving.',
            },
            {
                title: 'Final Approval Power:',
                description:
                    'No ticket can be permanently marked solved without verified resident confirmation.',
            },
        ],
        metricLabel: 'Tenant Rating:',
        metric: '4.9 / 5.0 satisfaction',
        metricSuffix: ' on repair closures.',
    },
    {
        eyebrow: 'Focused Execution',
        title: 'Field Technician',
        icon: Wrench,
        points: [
            {
                title: 'Job Priority Queue:',
                description:
                    'Clean, focused queue organized strictly by geographic location and urgency level.',
            },
            {
                title: 'One-Tap Flow:',
                description:
                    'Start job, record internal troubleshooting notes, and attach receipts seamlessly.',
            },
            {
                title: 'Visual Proof Capture:',
                description:
                    'Camera integration directly inside the app to log before and after states.',
            },
        ],
        metricLabel: 'Productivity:',
        metric: '38% reduction',
        metricSuffix: ' in operational idle time.',
    },
];

type SectionHeadingProps = {
    eyebrow: string;
    title: string;
    description?: string;
};

function SectionHeading({ eyebrow, title, description }: SectionHeadingProps) {
    return (
        <div className="mx-auto max-w-3xl text-center">
            <span className="text-[11px] font-semibold tracking-[0.16em] text-primary uppercase">
                {eyebrow}
            </span>
            <h2 className="mt-2 text-3xl leading-tight font-semibold tracking-[-0.025em] sm:text-4xl">
                {title}
            </h2>
            {description && (
                <p className="mt-4 text-base leading-7 text-muted-foreground sm:text-lg">
                    {description}
                </p>
            )}
        </div>
    );
}

export function TrustStrip() {
    const companies = [
        { name: 'Oakridge Residential', icon: Building2 },
        { name: 'Meridian Property Co.', icon: Handshake },
        { name: 'Keystone Urban', icon: Building2 },
        { name: 'Northstar Facilities', icon: Building2 },
    ];

    return (
        <section className="bg-secondary px-4 py-10 md:px-8">
            <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-8 lg:flex-row">
                <div className="text-center lg:text-left">
                    <span className="block text-[11px] font-semibold tracking-[0.12em] text-secondary-foreground uppercase">
                        Trusted in active operations
                    </span>
                    <span className="mt-1 block text-xs text-muted-foreground">
                        Managing 3,400+ doors across multi-family properties
                    </span>
                </div>
                <div className="flex flex-wrap items-center justify-center gap-x-8 gap-y-5 lg:gap-x-12">
                    {companies.map(({ name, icon: Icon }) => (
                        <div
                            key={name}
                            className="flex items-center gap-2 font-semibold tracking-tight"
                        >
                            <Icon className="size-5 text-primary" />
                            <span>{name}</span>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

export function ComparisonSection() {
    const disconnected = [
        {
            title: 'Scattered communication channels',
            description:
                'Texts, voicemails, and unformatted emails leave no traceable record or timestamps for dispatch audits.',
        },
        {
            title: 'Zero mandatory photo evidence',
            description:
                'Contractors invoice for blind repairs without verified before-and-after photographic documentation.',
        },
        {
            title: 'Unilateral completion marking',
            description:
                'Tickets are closed in the back office while the tenant is still experiencing unresolved water leakage.',
        },
    ];
    const connected = [
        {
            title: 'Single immutable thread of truth',
            description:
                'Every status shift, internal note, and dispatch assignation lives in a synchronized ledger visible to all parties.',
        },
        {
            title: 'Required photo verification gates',
            description:
                "Technicians cannot trigger 'Complete' states without timestamped, geo-tagged resolution photography.",
        },
        {
            title: 'Explicit resident sign-off loop',
            description:
                "Jobs can only transition to 'Closed' once the occupant confirms functional resolution via the mobile interface.",
        },
    ];

    return (
        <section className="px-4 py-20 md:px-8">
            <div className="mx-auto max-w-7xl">
                <SectionHeading
                    eyebrow="Systemic Friction Uncovered"
                    title="Why standard maintenance chains break down"
                    description="Property degradation and tenant turnover rarely happen because of equipment failure. They happen because of communication lapses between dispatch, contractors, and residents."
                />

                <div className="mt-16 grid gap-8 md:grid-cols-2">
                    <article className="flex flex-col justify-between rounded-2xl border border-rose-200/60 bg-rose-50/55 p-6 shadow-sm sm:p-8">
                        <div>
                            <div className="mb-6 flex items-center gap-3">
                                <span className="flex size-10 items-center justify-center rounded-xl bg-destructive text-white">
                                    <ImageOff className="size-5" />
                                </span>
                                <div>
                                    <h3 className="font-semibold">
                                        The Disconnected Process
                                    </h3>
                                    <p className="text-xs font-medium text-destructive">
                                        Standard fragmented workflow
                                    </p>
                                </div>
                            </div>
                            <ul className="grid gap-6">
                                {disconnected.map((item) => (
                                    <li
                                        key={item.title}
                                        className="flex items-start gap-4"
                                    >
                                        <CircleX className="mt-0.5 size-5 shrink-0 text-destructive" />
                                        <div>
                                            <strong className="block font-semibold">
                                                {item.title}
                                            </strong>
                                            <span className="mt-1 block text-sm leading-6 text-muted-foreground">
                                                {item.description}
                                            </span>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <div className="mt-8 rounded-xl bg-card/70 p-4">
                            <span className="block text-[11px] font-semibold tracking-wider text-destructive uppercase">
                                Operational Cost
                            </span>
                            <span className="mt-1 block text-sm">
                                Average 14-day turnaround times and 38%
                                duplicate dispatch rate.
                            </span>
                        </div>
                    </article>

                    <article className="flex flex-col justify-between rounded-2xl border border-border/70 bg-card p-6 shadow-[0_12px_35px_rgba(20,45,32,0.08)] sm:p-8">
                        <div>
                            <div className="mb-6 flex items-center gap-3">
                                <span className="flex size-10 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                                    <CheckCircle2 className="size-5" />
                                </span>
                                <div>
                                    <h3 className="font-semibold">
                                        The Property Maintenance Sequence
                                    </h3>
                                    <p className="text-xs text-muted-foreground">
                                        Synchronized lifecycle engine
                                    </p>
                                </div>
                            </div>
                            <div className="grid gap-4">
                                {connected.map((item) => (
                                    <div
                                        key={item.title}
                                        className="flex items-start gap-3 rounded-xl bg-secondary p-3.5"
                                    >
                                        <Check className="mt-0.5 size-4 shrink-0 text-primary" />
                                        <div>
                                            <h4 className="text-sm font-semibold">
                                                {item.title}
                                            </h4>
                                            <p className="mt-0.5 text-xs leading-5 text-muted-foreground">
                                                {item.description}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="mt-8 rounded-xl bg-secondary p-4">
                            <span className="block text-[11px] font-semibold tracking-wider text-primary uppercase">
                                Operational Reality
                            </span>
                            <p className="mt-1 text-sm text-muted-foreground">
                                [Proof Metric Placeholder: Operational
                                Turnaround Time Metric] — 72% reduction in
                                overall cycle time.
                            </p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    );
}

export function CapabilitiesSection() {
    return (
        <section
            id="features"
            className="scroll-mt-20 bg-secondary px-4 py-20 md:px-8"
        >
            <div className="mx-auto max-w-7xl">
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                    <div>
                        <span className="text-[11px] font-semibold tracking-[0.16em] text-primary uppercase">
                            Platform Architecture
                        </span>
                        <h2 className="mt-2 text-3xl font-semibold tracking-[-0.025em] sm:text-4xl">
                            Built for uncompromising operational discipline
                        </h2>
                    </div>
                    <p className="max-w-md text-sm leading-6 text-muted-foreground">
                        Six foundational modules engineered to standardize work
                        quality, protect asset values, and reduce operational
                        overhead.
                    </p>
                </div>

                <div className="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {capabilities.map(
                        ({ icon: Icon, title, description, label }) => (
                            <article
                                key={title}
                                className="interactive-card flex min-h-72 flex-col justify-between rounded-2xl border border-border/70 bg-card p-6 shadow-sm sm:p-8"
                            >
                                <div>
                                    <span className="mb-5 flex size-12 items-center justify-center rounded-xl bg-accent text-primary">
                                        <Icon className="size-6" />
                                    </span>
                                    <h3 className="text-lg font-semibold">
                                        {title}
                                    </h3>
                                    <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                        {description}
                                    </p>
                                </div>
                                <div className="mt-6 flex items-center gap-2 border-t border-border pt-4 text-sm font-semibold text-primary">
                                    {label}
                                    <ChevronRight className="size-4" />
                                </div>
                            </article>
                        ),
                    )}
                </div>
            </div>
        </section>
    );
}

export function WorkflowSection() {
    return (
        <section id="workflow" className="scroll-mt-20 px-4 py-20 md:px-8">
            <div className="mx-auto max-w-7xl">
                <SectionHeading
                    eyebrow="Sequential Integrity"
                    title="The 5-Step Maintenance Workflow"
                    description="A closed-loop operational sequence ensuring complete accountability at every milestone."
                />

                <div className="mt-16 grid gap-4 md:grid-cols-5">
                    {workflow.map((step, index) => {
                        const Icon = step.icon;

                        return (
                            <article
                                key={step.title}
                                className="interactive-card flex min-h-64 flex-col justify-between rounded-2xl border border-border/70 bg-card p-5 shadow-sm"
                            >
                                <div>
                                    <div className="mb-4 flex items-center justify-between">
                                        <span className="flex size-8 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">
                                            {index + 1}
                                        </span>
                                        <span className="rounded bg-secondary px-2 py-0.5 text-[10px] font-semibold text-muted-foreground uppercase">
                                            Step{' '}
                                            {String(index + 1).padStart(2, '0')}
                                        </span>
                                    </div>
                                    <h3 className="font-semibold">
                                        {step.title}
                                    </h3>
                                    <p className="mt-1 text-xs leading-5 text-muted-foreground">
                                        {step.description}
                                    </p>
                                </div>
                                <div className="mt-6 flex items-center gap-1.5 border-t border-border pt-3 text-xs font-medium text-primary">
                                    <Icon className="size-4" />
                                    {step.label}
                                </div>
                            </article>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

export function RolesSection() {
    return (
        <section
            id="roles"
            className="scroll-mt-20 bg-secondary px-4 py-20 md:px-8"
        >
            <div className="mx-auto max-w-7xl">
                <SectionHeading
                    eyebrow="Tailored Interfaces"
                    title="Purpose-built views for every stakeholder"
                    description="No convoluted all-in-one panels. Each role gets a sharp, dedicated workspace focused strictly on their immediate responsibilities."
                />

                <div className="mt-16 grid gap-8 lg:grid-cols-3">
                    {roles.map((role) => {
                        const Icon = role.icon;

                        return (
                            <article
                                key={role.title}
                                className="flex flex-col overflow-hidden rounded-2xl border border-border/70 bg-card shadow-sm"
                            >
                                <div className="flex items-center gap-4 border-b border-border p-6">
                                    <span className="flex size-12 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                                        <Icon className="size-6" />
                                    </span>
                                    <div>
                                        <span className="text-[10px] font-semibold tracking-wider text-primary uppercase">
                                            {role.eyebrow}
                                        </span>
                                        <h3 className="font-semibold">
                                            {role.title}
                                        </h3>
                                    </div>
                                </div>
                                <div className="flex flex-1 flex-col justify-between p-6">
                                    <ul className="grid gap-4 text-sm leading-6">
                                        {role.points.map((point) => (
                                            <li
                                                key={point.title}
                                                className="flex items-start gap-3"
                                            >
                                                <CheckCircle2 className="mt-0.5 size-5 shrink-0 text-primary" />
                                                <span>
                                                    <strong>
                                                        {point.title}
                                                    </strong>{' '}
                                                    {point.description}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                    <div className="mt-6 rounded-xl bg-secondary p-3.5 text-xs text-muted-foreground">
                                        {role.metricLabel}{' '}
                                        <strong className="text-foreground">
                                            {role.metric}
                                        </strong>
                                        {role.metricSuffix}
                                    </div>
                                </div>
                            </article>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

type AuthenticatedSectionProps = {
    authenticated: boolean;
};

type PricingSectionProps = AuthenticatedSectionProps & {
    canManageBilling: boolean;
    plans: Plan[];
};

export function ProofPricingSection({
    authenticated,
    canManageBilling,
    plans,
}: PricingSectionProps) {
    return (
        <section id="pricing" className="scroll-mt-20 px-4 py-20 md:px-8">
            <div className="mx-auto grid max-w-7xl gap-16">
                <div className="grid items-center gap-8 rounded-2xl border border-border/70 bg-card p-6 shadow-[0_12px_35px_rgba(20,45,32,0.08)] md:grid-cols-3 md:p-12">
                    <div className="md:col-span-2">
                        <span className="text-[11px] font-semibold tracking-[0.16em] text-primary uppercase">
                            Measurable Operational Lift
                        </span>
                        <h2 className="mt-2 text-3xl font-semibold tracking-[-0.025em] sm:text-4xl">
                            Average resolution turnaround compressed from 4.2
                            days to under 18 hours
                        </h2>
                        <p className="mt-3 text-base leading-7 text-muted-foreground sm:text-lg">
                            By enforcing clear diagnostics at submission and
                            removing dispatch phone loops, portfolios using the
                            platform reduce tenant escalations by 74%.
                        </p>
                    </div>
                    <div className="flex flex-col items-center justify-center rounded-xl bg-secondary p-6 text-center">
                        <span className="text-4xl font-bold tracking-tight text-primary">
                            17.8h
                        </span>
                        <span className="mt-1 text-sm font-medium">
                            Mean Time to Resolution
                        </span>
                        <span className="mt-2 text-xs font-semibold text-primary">
                            ↓ 81% reduction in idle ticket lag
                        </span>
                    </div>
                </div>

                <div className="grid gap-8 lg:grid-cols-12">
                    <article className="flex flex-col justify-between rounded-2xl border border-border/70 bg-card p-6 shadow-sm md:p-10 lg:col-span-7">
                        <div>
                            <div className="mb-6 flex items-center gap-1 text-primary">
                                {Array.from({ length: 5 }).map((_, index) => (
                                    <Star
                                        key={index}
                                        className="size-5 fill-current"
                                    />
                                ))}
                            </div>
                            <blockquote className="text-lg leading-8 sm:text-xl">
                                “Before this system, I managed 84 units across 7
                                buildings with sticky notes, WhatsApp groups,
                                and sheer adrenaline. Tickets slipped through,
                                residents were furious, and contractors billed
                                whatever they wanted. In 60 days, we achieved
                                zero lost requests and cut maintenance expenses
                                by 22%.”
                            </blockquote>
                        </div>
                        <div className="mt-6 flex items-center gap-4 border-t border-border pt-6">
                            <span className="flex size-12 items-center justify-center rounded-full bg-primary font-semibold text-primary-foreground">
                                DK
                            </span>
                            <div>
                                <h3 className="text-sm font-semibold">
                                    David Kowalski
                                </h3>
                                <p className="text-xs text-muted-foreground">
                                    Managing Partner, Kowalski Properties • 84
                                    Units
                                </p>
                            </div>
                        </div>
                    </article>

                    <div className="grid gap-6 lg:col-span-5">
                        {plans.map((plan) => (
                            <article
                                key={plan.id}
                                className="flex flex-col justify-between rounded-2xl border border-border/70 bg-card p-6 shadow-sm md:p-10"
                            >
                                <div>
                                    <div className="mb-4 flex items-center justify-between gap-4">
                                        <span className="text-[11px] font-semibold tracking-[0.16em] text-primary uppercase">
                                            Transparent Pricing
                                        </span>
                                        <span className="rounded-full bg-accent px-2.5 py-1 text-[10px] font-semibold text-accent-foreground">
                                            {plan.is_featured
                                                ? 'Most Popular'
                                                : 'Available Plan'}
                                        </span>
                                    </div>
                                    <h3 className="text-2xl font-semibold">
                                        {plan.name}
                                    </h3>
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        {plan.description}
                                    </p>
                                    <div className="my-6 flex items-baseline gap-2 border-b border-border pb-6">
                                        <span className="text-4xl font-bold tracking-tight">
                                            {(
                                                plan.unit_amount / 100
                                            ).toLocaleString('en-US', {
                                                style: 'currency',
                                                currency: plan.currency,
                                            })}
                                        </span>
                                        <span className="text-sm text-muted-foreground">
                                            / unit / {plan.billing_interval}
                                        </span>
                                    </div>
                                    <ul className="mb-8 grid gap-3 text-sm">
                                        {plan.features.map((feature) => (
                                            <li
                                                key={feature.key}
                                                className="flex items-center gap-2.5"
                                            >
                                                <Check className="size-4 text-primary" />
                                                {feature.value === true
                                                    ? feature.label
                                                    : `${feature.label}: ${feature.value}`}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                                <LandingAuthActions
                                    authenticated={authenticated}
                                    placement="pricing"
                                    planSlug={plan.slug}
                                    canManageBilling={canManageBilling}
                                />
                            </article>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}

export function FinalCtaSection({ authenticated }: AuthenticatedSectionProps) {
    return (
        <section className="bg-secondary px-4 py-16 md:px-8">
            <div className="mx-auto max-w-5xl rounded-2xl border border-border/70 bg-card p-6 text-center shadow-[0_20px_55px_rgba(20,45,32,0.12)] sm:p-10 md:p-14">
                <div className="inline-flex items-center gap-2 rounded-full bg-accent px-3 py-1.5">
                    <Bolt className="size-4 text-primary" />
                    <span className="text-[10px] font-semibold tracking-wider uppercase">
                        Setup in under 15 minutes
                    </span>
                </div>
                <h2 className="mx-auto mt-6 max-w-2xl text-3xl leading-tight font-semibold tracking-[-0.03em] sm:text-4xl">
                    Bring clarity and accountability to your maintenance
                    operations.
                </h2>
                <p className="mx-auto mt-4 max-w-xl text-base leading-7 text-muted-foreground sm:text-lg">
                    Say goodbye to misplaced requests and disputed repair bills.
                    Connect owners, residents, and technicians inside one
                    reliable system.
                </p>
                <LandingAuthActions
                    authenticated={authenticated}
                    className="mt-8 justify-center"
                />
                <div className="mt-8 flex flex-col items-center justify-center gap-3 text-xs text-muted-foreground sm:flex-row sm:gap-6">
                    {[
                        'No credit card required',
                        'Instant property import',
                        'Cancel anytime',
                    ].map((item) => (
                        <span key={item} className="flex items-center gap-1.5">
                            <Check className="size-4 text-primary" />
                            {item}
                        </span>
                    ))}
                </div>
            </div>
        </section>
    );
}
