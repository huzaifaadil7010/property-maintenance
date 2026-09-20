import { Head, useForm } from '@inertiajs/react';
import { Check, CreditCard, Plus, RefreshCw, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import AddPaymentMethodDialogue from '@/components/billing/add-payment-method-dialogue';
import SubscriptionCheckoutDialogue from '@/components/billing/subscription-checkout-dialogue';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import type {
    Plan,
    StoredPaymentMethod,
    Subscription,
    UnitCreationAllowance,
} from '@/types';
import BillingController from '@/wayfinder/App/Http/Controllers/Settings/BillingController';
import CancelSubscriptionController from '@/wayfinder/App/Http/Controllers/Settings/CancelSubscriptionController';
import DeletePaymentMethodController from '@/wayfinder/App/Http/Controllers/Settings/DeletePaymentMethodController';
import ResumeSubscriptionController from '@/wayfinder/App/Http/Controllers/Settings/ResumeSubscriptionController';
import SetDefaultPaymentMethodController from '@/wayfinder/App/Http/Controllers/Settings/SetDefaultPaymentMethodController';
import SwitchSubscriptionPlanController from '@/wayfinder/App/Http/Controllers/Settings/SwitchSubscriptionPlanController';

type Props = {
    stripeKey: string;
    plans: { data: Plan[] };
    unitCreationAllowance: UnitCreationAllowance | null;
    paymentMethods: { data: StoredPaymentMethod[] };
    subscription: Subscription | null;
};

const money = (amount: number, currency: string) =>
    (amount / 100).toLocaleString('en-US', {
        style: 'currency',
        currency,
    });

export default function Billing({
    stripeKey,
    plans: planResource,
    unitCreationAllowance,
    paymentMethods: paymentMethodResource,
    subscription,
}: Props) {
    const plans = planResource.data;
    const paymentMethods = paymentMethodResource.data;
    const params = new URLSearchParams(window.location.search);
    const requestedPlan = params.get('plan');
    const initialPlan =
        plans.find((plan) => plan.slug === requestedPlan) ?? plans[0] ?? null;
    const [selectedPlan, setSelectedPlan] = useState<Plan | null>(null);
    const [addingPaymentMethod, setAddingPaymentMethod] = useState(false);
    const cancelForm = useForm({});
    const resumeForm = useForm({});
    const switchForm = useForm({ plan_id: subscription?.plan_id ?? 0 });
    const defaultForm = useForm({});
    const deleteForm = useForm({});
    const currentPlan = useMemo(
        () => plans.find((plan) => plan.id === subscription?.plan_id),
        [plans, subscription?.plan_id],
    );
    const cancelErrors = cancelForm.errors as Record<
        string,
        string | undefined
    >;
    const resumeErrors = resumeForm.errors as Record<
        string,
        string | undefined
    >;
    const switchErrors = switchForm.errors as Record<
        string,
        string | undefined
    >;
    const defaultErrors = defaultForm.errors as Record<
        string,
        string | undefined
    >;
    const deleteErrors = deleteForm.errors as Record<
        string,
        string | undefined
    >;

    const choosePlan = (plan: Plan) => {
        if (!subscription || !subscription.is_valid) {
            setSelectedPlan(plan);

            return;
        }

        switchForm.transform(() => ({ plan_id: plan.id }));
        switchForm.patch(SwitchSubscriptionPlanController().url);
    };

    return (
        <>
            <Head title="Billing settings" />
            <h1 className="sr-only">Billing settings</h1>

            <div className="grid gap-8">
                <Heading
                    variant="small"
                    title="Billing"
                    description="Manage your organization’s plan, recurring billing, and saved cards."
                />

                {subscription && (
                    <section className="rounded-2xl border border-primary/15 bg-primary/6 p-5">
                        <div className="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p className="text-xs font-semibold tracking-wider text-primary uppercase">
                                    Current subscription
                                </p>
                                <h2 className="mt-1 text-xl font-semibold">
                                    {subscription.plan_name ??
                                        currentPlan?.name ??
                                        'Subscription'}
                                </h2>
                                <p className="mt-1 text-sm text-muted-foreground capitalize">
                                    {subscription.status.replaceAll('_', ' ')}
                                </p>
                                {subscription.trial_ends_at && (
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Trial ends{' '}
                                        {new Date(
                                            subscription.trial_ends_at,
                                        ).toLocaleDateString()}
                                    </p>
                                )}
                                {subscription.ends_at && (
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Access ends{' '}
                                        {new Date(
                                            subscription.ends_at,
                                        ).toLocaleDateString()}
                                    </p>
                                )}
                            </div>
                            {subscription.on_grace_period ? (
                                <Button
                                    variant="outline"
                                    disabled={resumeForm.processing}
                                    onClick={() =>
                                        resumeForm.patch(
                                            ResumeSubscriptionController().url,
                                        )
                                    }
                                >
                                    <RefreshCw /> Resume subscription
                                </Button>
                            ) : subscription.is_valid ? (
                                <Button
                                    variant="outline"
                                    disabled={cancelForm.processing}
                                    onClick={() =>
                                        cancelForm.patch(
                                            CancelSubscriptionController().url,
                                        )
                                    }
                                >
                                    Cancel recurring billing
                                </Button>
                            ) : null}
                        </div>
                        <InputError
                            className="mt-3"
                            message={
                                cancelErrors.subscription ??
                                resumeErrors.subscription
                            }
                        />
                    </section>
                )}

                {unitCreationAllowance && (
                    <section className="rounded-2xl border border-border bg-card p-5 shadow-sm">
                        <p className="text-xs font-semibold tracking-wider text-primary uppercase">
                            Unit creation allowance
                        </p>
                        <p className="mt-2 text-2xl font-semibold">
                            {unitCreationAllowance.used} /{' '}
                            {unitCreationAllowance.limit} used
                        </p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {unitCreationAllowance.remaining} unit creations
                            remain until{' '}
                            {new Date(
                                unitCreationAllowance.period_ends_at,
                            ).toLocaleDateString()}
                            .
                        </p>
                    </section>
                )}

                <section className="grid gap-4">
                    <div>
                        <h2 className="font-semibold">Available plans</h2>
                        <p className="text-sm text-muted-foreground">
                            One fixed monthly price with no per-unit billing.
                        </p>
                    </div>
                    <div className="grid gap-4 md:grid-cols-2">
                        {plans.map((plan) => {
                            const isCurrent = subscription?.plan_id === plan.id;

                            return (
                                <article
                                    key={plan.id}
                                    className="flex flex-col rounded-2xl border border-border bg-card p-5 shadow-sm"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 className="text-lg font-semibold">
                                                {plan.name}
                                            </h3>
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                {plan.description}
                                            </p>
                                        </div>
                                        {isCurrent && (
                                            <span className="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                                                Current
                                            </span>
                                        )}
                                    </div>
                                    <p className="mt-5 text-3xl font-bold">
                                        {money(plan.amount, plan.currency)}
                                        <span className="text-sm font-normal text-muted-foreground">
                                            {' '}
                                            / {plan.billing_interval}
                                        </span>
                                    </p>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Includes up to{' '}
                                        {plan.unit_creation_limit} unit
                                        creations per billing cycle.
                                    </p>
                                    <ul className="my-5 grid gap-2 text-sm">
                                        {plan.features.map((feature) => (
                                            <li
                                                key={feature.key}
                                                className="flex gap-2"
                                            >
                                                <Check className="mt-0.5 size-4 shrink-0 text-primary" />
                                                {feature.value === true
                                                    ? feature.label
                                                    : `${feature.label}: ${feature.value}`}
                                            </li>
                                        ))}
                                    </ul>
                                    <Button
                                        className="mt-auto"
                                        disabled={
                                            isCurrent ||
                                            switchForm.processing
                                        }
                                        onClick={() => choosePlan(plan)}
                                    >
                                        {isCurrent
                                            ? 'Current plan'
                                            : subscription?.is_valid
                                              ? 'Switch plan'
                                              : `Start ${plan.trial_days}-day trial`}
                                    </Button>
                                </article>
                            );
                        })}
                    </div>
                    <InputError
                        message={
                            switchErrors.plan_id ?? switchErrors.subscription
                        }
                    />
                </section>

                <section className="grid gap-4">
                    <div className="flex items-center justify-between gap-4">
                        <div>
                            <h2 className="font-semibold">Payment methods</h2>
                            <p className="text-sm text-muted-foreground">
                                Cards are stored securely with Stripe.
                            </p>
                        </div>
                        <Button
                            variant="outline"
                            onClick={() => setAddingPaymentMethod(true)}
                        >
                            <Plus /> Add card
                        </Button>
                    </div>
                    <div className="grid gap-3">
                        {paymentMethods.length === 0 && (
                            <p className="rounded-xl border border-dashed p-5 text-sm text-muted-foreground">
                                No saved payment methods yet.
                            </p>
                        )}
                        {paymentMethods.map((method) => (
                            <div
                                key={method.id}
                                className="flex flex-wrap items-center gap-3 rounded-xl border border-border p-4"
                            >
                                <CreditCard className="text-primary" />
                                <div>
                                    <p className="font-medium capitalize">
                                        {method.brand} •••• {method.last_four}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Expires {method.exp_month}/
                                        {method.exp_year}
                                    </p>
                                </div>
                                {method.is_default && (
                                    <span className="rounded-full bg-accent px-2 py-1 text-xs font-medium">
                                        Default
                                    </span>
                                )}
                                <div className="ml-auto flex gap-2">
                                    {!method.is_default && (
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            disabled={defaultForm.processing}
                                            onClick={() =>
                                                defaultForm.patch(
                                                    SetDefaultPaymentMethodController(
                                                        method.id,
                                                    ).url,
                                                )
                                            }
                                        >
                                            Make default
                                        </Button>
                                    )}
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        disabled={
                                            deleteForm.processing ||
                                            method.is_default ||
                                            subscription?.payment_method_id ===
                                                method.id
                                        }
                                        onClick={() =>
                                            deleteForm.delete(
                                                DeletePaymentMethodController(
                                                    method.id,
                                                ).url,
                                            )
                                        }
                                        aria-label="Delete payment method"
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>
                            </div>
                        ))}
                    </div>
                    <InputError
                        message={
                            defaultErrors.payment_method ??
                            deleteErrors.payment_method
                        }
                    />
                </section>
            </div>

            <SubscriptionCheckoutDialogue
                open={Boolean(selectedPlan)}
                onOpenChange={(open) => !open && setSelectedPlan(null)}
                plan={selectedPlan ?? initialPlan}
                paymentMethods={paymentMethods}
                stripeKey={stripeKey}
            />
            <AddPaymentMethodDialogue
                open={addingPaymentMethod}
                onOpenChange={setAddingPaymentMethod}
                stripeKey={stripeKey}
            />
        </>
    );
}

Billing.layout = {
    breadcrumbs: [
        { title: 'Billing settings', href: BillingController.index() },
    ],
};
