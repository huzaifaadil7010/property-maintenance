import { Head } from '@inertiajs/react';
import { Sun } from 'lucide-react';
import Heading from '@/components/heading';
import { edit as editAppearance } from '@/wayfinder/routes/appearance';

export default function Appearance() {
    return (
        <>
            <Head title="Appearance settings" />

            <h1 className="sr-only">Appearance settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Appearance settings"
                    description="The application is temporarily using its light presentation."
                />
                <div className="flex items-start gap-4 rounded-2xl border border-primary/15 bg-primary/6 p-5">
                    <div className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <Sun className="size-5" aria-hidden="true" />
                    </div>
                    <div className="grid gap-1">
                        <p className="font-semibold">Light theme is active</p>
                        <p className="text-sm leading-6 text-muted-foreground">
                            Theme switching is paused while the new light visual
                            system is being finalized.
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'Appearance settings',
            href: editAppearance(),
        },
    ],
};
