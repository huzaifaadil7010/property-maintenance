import type { Auth } from '@/types/auth';
import type { SubscriptionAccess } from '@/types/billing';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            currentOrganization: Pick<
                App.Models.Organization,
                'uuid' | 'name'
            > | null;
            temp_path: string;
            sidebarOpen: boolean;
            subscriptionAccess: SubscriptionAccess;
            [key: string]: unknown;
        };
    }
}
