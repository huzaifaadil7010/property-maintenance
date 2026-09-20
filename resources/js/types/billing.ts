export type PlanFeature = {
    key: string;
    label: string;
    value: boolean | number | string;
};

export type Plan = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    unit_amount: number;
    currency: string;
    billing_interval: string;
    billing_interval_count: number;
    trial_days: number;
    is_featured: boolean;
    features: PlanFeature[];
};

export type StoredPaymentMethod = {
    id: number;
    type: string;
    brand: string | null;
    last_four: string | null;
    exp_month: number | null;
    exp_year: number | null;
    is_default: boolean;
};

export type Subscription = {
    id: number;
    plan_id: number | null;
    plan_name: string | null;
    payment_method_id: number | null;
    status: string;
    quantity: number | null;
    trial_ends_at: string | null;
    ends_at: string | null;
    is_valid: boolean;
    is_canceled: boolean;
    on_grace_period: boolean;
};

export type SubscriptionAccess = {
    state: string;
    hasPanelAccess: boolean;
    canManageBilling: boolean;
    message: string | null;
};
