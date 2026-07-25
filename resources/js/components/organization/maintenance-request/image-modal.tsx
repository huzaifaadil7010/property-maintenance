import { X } from 'lucide-react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface ImageModalProps {
    isOpen: boolean;
    onClose: () => void;
    imageUrl: string;
    imageName: string;
}

export function ImageModal({
    isOpen,
    onClose,
    imageUrl,
    imageName,
}: ImageModalProps) {
    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-4xl border-slate-200 dark:border-slate-800 bg-slate-950">
                <DialogHeader>
                    <DialogTitle className="text-white">{imageName}</DialogTitle>
                </DialogHeader>
                <div className="flex items-center justify-center min-h-96">
                    <img
                        src={imageUrl}
                        alt={imageName}
                        className="max-w-full max-h-[80vh] object-contain rounded-lg"
                    />
                </div>
            </DialogContent>
        </Dialog>
    );
}
