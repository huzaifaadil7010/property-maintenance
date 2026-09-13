import { Head, usePage } from '@inertiajs/react';
import { LandingFooter } from '@/components/landing-page/landing-footer';
import { LandingHeader } from '@/components/landing-page/landing-header';
import { LandingHero } from '@/components/landing-page/landing-hero';
import {
    CapabilitiesSection,
    ComparisonSection,
    FinalCtaSection,
    ProofPricingSection,
    RolesSection,
    TrustStrip,
    WorkflowSection,
} from '@/components/landing-page/landing-sections';

export default function Welcome() {
    const { auth } = usePage().props;
    const authenticated = Boolean(auth.user);

    return (
        <>
            <Head title="Property Maintenance">
                <meta
                    name="description"
                    content="One accountable workflow for residential maintenance operations."
                />
            </Head>

            <div
                id="top"
                className="min-h-dvh overflow-x-hidden bg-background text-foreground"
            >
                <LandingHeader authenticated={authenticated} />
                <main className="pt-20">
                    <LandingHero authenticated={authenticated} />
                    <TrustStrip />
                    <ComparisonSection />
                    <CapabilitiesSection />
                    <WorkflowSection />
                    <RolesSection />
                    <ProofPricingSection authenticated={authenticated} />
                    <FinalCtaSection authenticated={authenticated} />
                </main>
                <LandingFooter />
            </div>
        </>
    );
}
