import { useForm, useHttp } from '@inertiajs/react';
import {
    Elements,
    PaymentElement,
    useElements,
    useStripe,
} from '@stripe/react-stripe-js';
import { loadStripe } from '@stripe/stripe-js';
import { LoaderCircle } from 'lucide-react';
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
import CreateSubscriptionIntentController from '@/wayfinder/App/Http/Controllers/Settings/CreateSubscriptionIntentController';
import StorePaymentMethodController from '@/wayfinder/App/Http/Controllers/Settings/StorePaymentMethodController';

function PaymentMethodForm({ onSuccess }: { onSuccess: () => void }) {
    const stripe = useStripe();
    const elements = useElements();
    const [ready, setReady] = useState(false);
    const [confirming, setConfirming] = useState(false);
    const form = useForm({ payment_method: '' });

    const submit = async (event: React.FormEvent) => {
        event.preventDefault();

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
                'payment_method',
                error?.message ?? 'The card could not be confirmed.',
            );

            return;
        }

        form.transform(() => ({
            payment_method: setupIntent.payment_method as string,
        }));
        form.post(StorePaymentMethodController().url, { onSuccess });
    };

    return (
        <form onSubmit={submit} className="grid gap-5">
            <div className="rounded-xl border border-border p-4">
                <PaymentElement onReady={() => setReady(true)} />
            </div>
            <InputError message={form.errors.payment_method} />
            <DialogFooter>
                <Button disabled={!ready || confirming || form.processing}>
                    {(confirming || form.processing) && (
                        <LoaderCircle className="animate-spin" />
                    )}
                    Save card
                </Button>
            </DialogFooter>
        </form>
    );
}

export default function AddPaymentMethodDialogue({
    open,
    onOpenChange,
    stripeKey,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    stripeKey: string;
}) {
    const [clientSecret, setClientSecret] = useState<string | null>(null);
    const stripePromise = useMemo(() => loadStripe(stripeKey), [stripeKey]);
    const intentForm = useHttp<
        Record<string, never>,
        { client_secret: string }
    >({});

    useEffect(() => {
        if (!open) {
            return;
        }

        intentForm.post(CreateSubscriptionIntentController().url, {
            onSuccess: ({ client_secret }) => setClientSecret(client_secret),
        });
        // Each open intentionally creates a new SetupIntent.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add payment method</DialogTitle>
                    <DialogDescription>
                        Your card details are securely handled by Stripe.
                    </DialogDescription>
                </DialogHeader>
                {intentForm.processing || !clientSecret ? (
                    <div className="flex min-h-36 items-center justify-center text-sm text-muted-foreground">
                        <LoaderCircle className="mr-2 animate-spin" />{' '}
                        Initializing secure card entry…
                    </div>
                ) : (
                    <Elements
                        key={clientSecret}
                        stripe={stripePromise}
                        options={{ clientSecret }}
                    >
                        <PaymentMethodForm
                            onSuccess={() => onOpenChange(false)}
                        />
                    </Elements>
                )}
            </DialogContent>
        </Dialog>
    );
}
