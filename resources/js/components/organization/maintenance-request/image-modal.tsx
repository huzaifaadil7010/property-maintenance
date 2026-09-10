import * as DialogPrimitive from '@radix-ui/react-dialog';
import { X } from 'lucide-react';

interface ImageModalProps {
    isOpen: boolean;
    onClose: () => void;
    imageUrl: string;
    imageSrcSet?: string | null;
    imageName: string;
}

export function ImageModal({
    isOpen,
    onClose,
    imageUrl,
    imageSrcSet,
    imageName,
}: ImageModalProps) {
    return (
        <DialogPrimitive.Root open={isOpen} onOpenChange={onClose}>
            <DialogPrimitive.Portal>
                <DialogPrimitive.Overlay className="fixed inset-0 z-50 bg-black/30 backdrop-blur-sm" />
                <DialogPrimitive.Content className="pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4 outline-none sm:p-8">
                    <DialogPrimitive.Title className="sr-only">
                        {imageName}
                    </DialogPrimitive.Title>
                    <DialogPrimitive.Description className="sr-only">
                        Preview of {imageName}
                    </DialogPrimitive.Description>
                    <img
                        src={imageUrl}
                        srcSet={imageSrcSet ?? undefined}
                        sizes="(max-width: 640px) calc(100vw - 2rem), calc(100vw - 4rem)"
                        alt={imageName}
                        className="pointer-events-auto h-auto max-h-full w-auto max-w-full rounded-lg object-contain shadow-2xl"
                    />
                    <DialogPrimitive.Close className="pointer-events-auto absolute top-4 right-4 rounded-xs p-1 text-white opacity-70 transition-opacity hover:bg-white/10 hover:opacity-100 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none">
                        <X className="size-5" />
                        <span className="sr-only">Close</span>
                    </DialogPrimitive.Close>
                </DialogPrimitive.Content>
            </DialogPrimitive.Portal>
        </DialogPrimitive.Root>
    );
}
