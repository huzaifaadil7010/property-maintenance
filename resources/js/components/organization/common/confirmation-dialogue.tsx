import type { ComponentProps, FormEvent, ReactNode } from 'react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';

type ConfirmationDialogueProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onSubmit: () => void;
    title: string;
    description: ReactNode;
    submitLabel?: string;
    submittingLabel?: string;
    cancelLabel?: string;
    submitVariant?: ComponentProps<typeof Button>['variant'];
    processing?: boolean;
    disabled?: boolean;
};

export default function ConfirmationDialogue({
    open,
    onOpenChange,
    onSubmit,
    title,
    description,
    submitLabel = 'Confirm',
    submittingLabel = 'Confirming',
    cancelLabel = 'Cancel',
    submitVariant = 'default',
    processing = false,
    disabled = false,
}: ConfirmationDialogueProps) {
    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        onSubmit();
    }

    function handleOpenChange(nextOpen: boolean) {
        if (processing && !nextOpen) {
            return;
        }

        onOpenChange(nextOpen);
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    <DialogDescription>{description}</DialogDescription>
                </DialogHeader>

                <form onSubmit={submit}>
                    <DialogFooter>
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="secondary"
                                disabled={processing}
                            >
                                {cancelLabel}
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            variant={submitVariant}
                            disabled={processing || disabled}
                        >
                            {processing && <Spinner />}
                            {processing ? submittingLabel : submitLabel}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
