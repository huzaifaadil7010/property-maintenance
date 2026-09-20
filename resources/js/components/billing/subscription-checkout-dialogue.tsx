import { useForm, useHttp } from '@inertiajs/react';
import {
    PaymentElement,
    Elements,
    useElements,
    useStripe,
} from '@stripe/react-stripe-js';
import { loadStripe } from '@stripe/stripe-js';
import { CreditCard, LoaderCircle } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { Plan, StoredPaymentMethod } from '@/types';
import CreateSubscriptionIntentController from '@/wayfinder/App/Http/Controllers/Settings/CreateSubscriptionIntentController';
import SubscriptionController from '@/wayfinder/App/Http/Controllers/Settings/SubscriptionController';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    plan: Plan | null;
    paymentMethods: StoredPaymentMethod[];
    stripeKey: string;
};

type IntentResponse = { client_secret: string };

function CheckoutForm({
    plan,
    paymentMethods,
    onSuccess,
}: {
    plan: Plan;
    paymentMethods: StoredPaymentMethod[];
    onSuccess: () => void;
}) {
    const stripe = useStripe();
    const elements = useElements();
    const defaultMethod = paymentMethods.find((method) => method.is_default);
    const [choice, setChoice] = useState<string>(
        defaultMethod ? String(defaultMethod.id) : 'new',
    );
    const [elementReady, setElementReady] = useState(false);
    const [confirming, setConfirming] = useState(false);
    const form = useForm({
        plan_id: plan.id,
        stored_payment_method_id: defaultMethod?.id ?? null,
        new_stripe_payment_method_id: null as string | null,
    });
    const formErrors = form.errors as Record<string, string | undefined>;

    const submit = async (event: React.FormEvent) => {
        event.preventDefault();
        form.clearErrors();

        if (choice !== 'new') {
            form.transform(() => ({
                plan_id: plan.id,
                stored_payment_method_id: Number(choice),
                new_stripe_payment_method_id: null,
            }));
            form.post(SubscriptionController.store().url, { onSuccess });

            return;
        }

        if (!stripe || !elements) {
            return;
        }

        setConfirming(true);
        const { error, setupIntent } = await stripe.confirmSetup({
            elements,
            redirect: 'if_required',
            confirmParams: { return_url: window.location.href },
        });
        setConfirming(false);

        if (error || typeof setupIntent?.payment_method !== 'string') {
            form.setError(
                'new_stripe_payment_method_id',
                error?.message ?? 'The card could not be confirmed.',
            );

            return;
        }

        form.transform(() => ({
            plan_id: plan.id,
            stored_payment_method_id: null,
            new_stripe_payment_method_id: setupIntent.payment_method as string,
        }));
        form.post(SubscriptionController.store().url, { onSuccess });
    };

    return (
        <form onSubmit={submit} className="grid gap-5">
            {paymentMethods.length > 0 && (
                <div className="grid gap-2">
                    {paymentMethods.map((method) => (
                        <label
                            key={method.id}
                            className="flex cursor-pointer items-center gap-3 rounded-xl border border-border p-3"
                        >
                            <input
                                type="radio"
                                name="payment-method"
                                checked={choice === String(method.id)}
                                onChange={() => setChoice(String(method.id))}
                            />
                            <CreditCard className="size-4 text-primary" />
                            <span className="text-sm font-medium capitalize">
                                {method.brand} •••• {method.last_four}
                            </span>
                            {method.is_default && (
                                <span className="ml-auto text-xs text-muted-foreground">
                                    Default
                                </span>
                            )}
                        </label>
                    ))}
                    <label className="flex cursor-pointer items-center gap-3 rounded-xl border border-border p-3">
                        <input
                            type="radio"
                            name="payment-method"
                            checked={choice === 'new'}
                            onChange={() => setChoice('new')}
                        />
                        <span className="text-sm font-medium">
                            Add a new card
                        </span>
                    </label>
                </div>
            )}

            {choice === 'new' && (
                <div className="rounded-xl border border-border p-4">
                    <PaymentElement
                        options={{ layout: 'tabs' }}
                        onReady={() => setElementReady(true)}
                    />
                </div>
            )}

            <InputError
                message={
                    formErrors.payment_method ??
                    formErrors.new_stripe_payment_method_id ??
                    formErrors.stored_payment_method_id ??
                    formErrors.subscription
                }
            />

            <DialogFooter>
                <Button
                    type="submit"
                    disabled={
                        form.processing ||
                        confirming ||
                        (choice === 'new' && (!stripe || !elementReady))
                    }
                >
                    {(form.processing || confirming) && (
                        <LoaderCircle className="animate-spin" />
                    )}
                    Start {plan.trial_days}-day trial
                </Button>
            </DialogFooter>
        </form>
    );
}

export default function SubscriptionCheckoutDialogue({
    open,
    onOpenChange,
    plan,
    paymentMethods,
    stripeKey,
}: Props) {
    const [clientSecret, setClientSecret] = useState<string | null>(null);
    const stripePromise = useMemo(() => loadStripe(stripeKey), [stripeKey]);
    const intentForm = useHttp<Record<string, never>, IntentResponse>({});

    useEffect(() => {
        if (!open || !plan) {
            return;
        }

        intentForm.post(CreateSubscriptionIntentController().url, {
            onSuccess: ({ client_secret }) => setClientSecret(client_secret),
        });
        // Each open and plan selection intentionally creates a new SetupIntent.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, plan?.id]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Subscribe to {plan?.name}</DialogTitle>
                    <DialogDescription>
                        Add or select a card. Billing starts after the trial.
                    </DialogDescription>
                </DialogHeader>

                {intentForm.processing || !clientSecret || !plan ? (
                    <div className="flex min-h-36 items-center justify-center text-sm text-muted-foreground">
                        <LoaderCircle className="mr-2 size-5 animate-spin" />
                        Initializing secure card entry…
                    </div>
                ) : (
                    <Elements
                        key={clientSecret}
                        stripe={stripePromise}
                        options={{ clientSecret }}
                    >
                        <CheckoutForm
                            plan={plan}
                            paymentMethods={paymentMethods}
                            onSuccess={() => onOpenChange(false)}
                        />
                    </Elements>
                )}
            </DialogContent>
        </Dialog>
    );
}
