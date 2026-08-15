import React, { useRef, useState } from 'react';
import { Upload, X } from 'lucide-react';
import { Label } from '@/components/ui/label';

export type ImageUploadPreview = {
    url: string;
    fileName: string;
    isProcessing?: boolean;
    progress?: number | null;
};

type ImageUploadInputProps = {
    label?: string;
    description?: string;
    placeholder?: string;
    disabled?: boolean;
    isProcessing?: boolean;
    progress?: number | null;
    previewUrl?: string | null;
    fileName?: string | null;
    onChange?: (value: File | File[]) => void;
    accept?: string;
    className?: string;
    multiple?: boolean;
    previews?: ImageUploadPreview[];
    onRemove?: ((index: number) => void) | null;
    maxFiles?: number;
};

const ImageUploadInput = ({
                              label = 'Upload Proof Image',
                              description = 'PNG, JPG, WEBP - MAX 10MB',
                              placeholder = 'Upload Image',
                              disabled = false,
                              isProcessing = false,
                              progress = null,
                              previewUrl = null,
                              fileName = null,
                              onChange,
                              accept = '.png,.jpg,.jpeg,.webp',
                              className = '',
                              // Multi-image props
                              multiple = false,
                              previews = [],
                              onRemove = null,
                          maxFiles = 10,
                      }: ImageUploadInputProps) => {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [isDragActive, setIsDragActive] = useState(false);

    // Determine if upload area should be shown in multi mode
    const canAddMore = !multiple || previews.length < maxFiles;
    const isAnyProcessing = multiple
        ? previews.some(p => p.isProcessing)
        : isProcessing;

    const handleClick = () => {
        if (!isAnyProcessing && !disabled && canAddMore) {
            fileInputRef.current?.click();
        }
    };

    const handleDrag = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        if (!isAnyProcessing && !disabled && canAddMore) {
            setIsDragActive(e.type === 'dragenter' || e.type === 'dragover');
        }
    };

    const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragActive(false);

        if (isAnyProcessing || disabled || !canAddMore) return;

        const files = e.dataTransfer.files;
        if (files?.length > 0) {
            if (multiple) {
                // Convert FileList to Array and respect maxFiles limit
                const filesArray = Array.from(files).slice(0, maxFiles - previews.length);
                if (onChange) {
                    onChange(filesArray);
                }
            } else {
                const file = files[0];
                if (onChange) {
                    onChange(file);
                }
            }
        }
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (multiple) {
            const files = e.target.files;
            if (files && onChange) {
                // Convert FileList to Array and respect maxFiles limit
                const filesArray = Array.from(files).slice(0, maxFiles - previews.length);
                onChange(filesArray);
            }
        } else {
            const file = e.target.files?.[0];
            if (file && onChange) {
                onChange(file);
            }
        }
    };

    const handleRemoveImage = (
        index: number,
        e: React.MouseEvent<HTMLButtonElement>,
    ) => {
        e.stopPropagation();
        if (onRemove) {
            onRemove(index);
        }
    };

    // Single image mode (original behavior - backward compatible)
    if (!multiple) {
        return (
            <div className={`form-field ${className}`}>
                {label && <Label className="label">{label}</Label>}

                <input
                    ref={fileInputRef}
                    type="file"
                    accept={accept}
                    className="hidden"
                    disabled={isProcessing || disabled}
                    onChange={handleInputChange}
                    aria-label={label}
                />

                <div
                    onClick={handleClick}
                    onDragEnter={handleDrag}
                    onDragLeave={handleDrag}
                    onDragOver={handleDrag}
                    onDrop={handleDrop}
                    className={`flex min-h-40 cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border border-dashed transition-colors ${
                        isDragActive
                            ? 'border-foreground/40 bg-secondary/70'
                            : 'border-foreground/20 bg-secondary/50 hover:bg-secondary/80'
                    } ${isProcessing || disabled ? 'cursor-not-allowed opacity-60' : ''} py-8`}
                >
                    {isProcessing ? (
                        <div className="flex flex-col items-center gap-3">
                            <div className="flex size-5 items-center justify-center">
                                <div
                                    className="size-5 animate-spin rounded-full border-2 border-primary border-t-transparent"
                                    role="status"
                                    aria-label="Uploading"
                                />
                            </div>
                            <p className="text-xs text-foreground/70">
                                Uploading
                                {progress !== null
                                    ? ` ${Math.round(progress)}%`
                                    : '...'}
                            </p>
                        </div>
                    ) : previewUrl ? (
                        <div className="flex w-full flex-col items-center gap-3">
                            <img
                                src={previewUrl}
                                alt="Uploaded image preview"
                                className="max-h-36 w-auto rounded-md object-contain"
                            />
                            {fileName && (
                                <p className="max-w-full px-2 text-[0.625rem] font-light break-all text-foreground/80">
                                    {fileName}
                                </p>
                            )}
                        </div>
                    ) : (
                        <>
                            <div className="mb-5 flex size-10 items-center justify-center rounded-lg border border-foreground/20 bg-foreground/10">
                                <Upload className="h-4 w-4 text-secondary-foreground/40" />
                            </div>
                            <p className="text-xs text-foreground">{placeholder}</p>
                            <p className="text-[0.625rem] font-light text-foreground/70">
                                {description}
                            </p>
                        </>
                    )}
                </div>
            </div>
        );
    }

    // Multi-image mode (new feature)
    return (
        <div className={`form-field ${className}`}>
            {label && <Label className="label">{label}</Label>}

            <input
                ref={fileInputRef}
                type="file"
                accept={accept}
                multiple
                className="hidden"
                disabled={isAnyProcessing || disabled}
                onChange={handleInputChange}
                aria-label={label}
            />

            <div className="flex flex-col gap-3">
                {/* Previews Grid */}
                {previews.length > 0 && (
                    <div className="flex flex-wrap gap-3">
                        {previews.map((preview, index) => (
                            <div
                                key={index}
                                className="relative flex flex-col items-center gap-2"
                            >
                                <div className="relative group">
                                    {/* Image Preview */}
                                    <div className="flex h-32 w-32 items-center justify-center overflow-hidden rounded-lg border border-foreground/20 bg-secondary/50 p-2">
                                        {preview.isProcessing ? (
                                            <div className="flex flex-col items-center gap-2">
                                                <div className="flex size-5 items-center justify-center">
                                                    <div
                                                        className="size-5 animate-spin rounded-full border-2 border-primary border-t-transparent"
                                                        role="status"
                                                        aria-label="Uploading"
                                                    />
                                                </div>
                                                {preview.progress !== null && preview.progress !== undefined && (
                                                    <p className="text-[0.625rem] text-foreground/70">
                                                        {Math.round(preview.progress)}%
                                                    </p>
                                                )}
                                            </div>
                                        ) : (
                                            <img
                                                src={preview.url}
                                                alt={preview.fileName || `Preview ${index + 1}`}
                                                className="h-full w-full object-contain"
                                            />
                                        )}
                                    </div>

                                    {/* Remove Button */}
                                    {!preview.isProcessing && onRemove && (
                                        <button
                                            type="button"
                                            onClick={(e) => handleRemoveImage(index, e)}
                                            className="absolute -right-2 -top-2 flex size-6 items-center justify-center rounded-full border border-foreground/20 bg-secondary/90 opacity-0 transition-opacity hover:bg-secondary group-hover:opacity-100"
                                            aria-label={`Remove image ${index + 1}`}
                                        >
                                            <X className="h-3.5 w-3.5 text-foreground/70" />
                                        </button>
                                    )}
                                </div>

                                {/* File Name */}
                                {preview.fileName && !preview.isProcessing && (
                                    <p className="w-32 truncate text-center text-[0.625rem] font-light text-foreground/80">
                                        {preview.fileName}
                                    </p>
                                )}
                            </div>
                        ))}
                    </div>
                )}

                {/* Upload Area - shown only if can add more */}
                {canAddMore && (
                    <div
                        onClick={handleClick}
                        onDragEnter={handleDrag}
                        onDragLeave={handleDrag}
                        onDragOver={handleDrag}
                        onDrop={handleDrop}
                        className={`flex min-h-40 cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border border-dashed transition-colors ${
                            isDragActive
                                ? 'border-foreground/40 bg-secondary/70'
                                : 'border-foreground/20 bg-secondary/50 hover:bg-secondary/80'
                        } ${isAnyProcessing || disabled ? 'cursor-not-allowed opacity-60' : ''} py-8`}
                    >
                        <div className="mb-5 flex size-10 items-center justify-center rounded-lg border border-foreground/20 bg-foreground/10">
                            <Upload className="h-4 w-4 text-secondary-foreground/40" />
                        </div>
                        <p className="text-xs text-foreground">{placeholder}</p>
                        <p className="text-[0.625rem] font-light text-foreground/70">
                            {description}
                        </p>
                        {maxFiles && (
                            <p className="text-[0.625rem] font-light text-foreground/60">
                                {previews.length} / {maxFiles} images
                            </p>
                        )}
                    </div>
                )}

                {/* Max Files Reached Message */}
                {!canAddMore && (
                    <div className="flex min-h-20 items-center justify-center rounded-lg border border-dashed border-foreground/20 bg-secondary/50 py-6">
                        <p className="text-xs text-foreground/70">
                            Maximum {maxFiles} images reached
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
};

export default ImageUploadInput;

/**
 * ImageUploadInput - A reusable image upload component supporting single and multiple images
 *
 * @component
 * @param {Object} props
 * @param {string} [props.label="Upload Image"] - Label text for the field
 * @param {string} [props.description="PNG, JPG, WEBP - MAX 10MB"] - Description text
 * @param {string} [props.placeholder="Upload Image"] - Placeholder instruction text
 * @param {boolean} [props.disabled=false] - Disable the upload field
 * @param {string} [props.accept=".png,.jpg,.jpeg,.webp"] - File type accept attribute
 * @param {string} [props.className=""] - Additional CSS classes
 *
 * SINGLE IMAGE MODE (default - backward compatible):
 * @param {boolean} [props.isProcessing=false] - Show loading state
 * @param {number} [props.progress=null] - Upload progress percentage (0-100)
 * @param {string} [props.previewUrl=null] - URL for image preview
 * @param {string} [props.fileName=null] - Name of the uploaded file
 * @param {Function} [props.onChange] - Callback when file is selected (receives File object)
 *
 * MULTI IMAGE MODE:
 * @param {boolean} [props.multiple=false] - Enable multiple image upload
 * @param {Array} [props.previews=[]] - Array of preview objects: [{url, fileName, isProcessing?, progress?}]
 * @param {Function} [props.onRemove] - Callback when image is removed (receives index)
 * @param {number} [props.maxFiles=10] - Maximum number of files allowed
 * @param {Function} [props.onChange] - Callback when files are selected (receives File[] array)
 *
 * @example Single Image Mode (backward compatible)
 * const [preview, setPreview] = useState(null);
 * const [isProcessing, setIsProcessing] = useState(false);
 * const [progress, setProgress] = useState(null);
 *
 * const handleFileChange = (file) => {
 *   setIsProcessing(true);
 *   // Upload file...
 *   setPreview(filePreviewUrl);
 *   setIsProcessing(false);
 * };
 *
 * <ImageUploadInput
 *   label="Upload Proof"
 *   placeholder="Upload Front Photo Of Card"
 *   previewUrl={preview}
 *   isProcessing={isProcessing}
 *   progress={progress}
 *   onChange={handleFileChange}
 * />
 *
 * @example Multi Image Mode
 * const [previews, setPreviews] = useState([]);
 *
 * const handleFilesChange = (files) => {
 *   const newPreviews = files.map(file => ({
 *     url: URL.createObjectURL(file),
 *     fileName: file.name,
 *     isProcessing: true,
 *     progress: 0
 *   }));
 *   setPreviews([...previews, ...newPreviews]);
 *   // Upload files...
 * };
 *
 * const handleRemove = (index) => {
 *   setPreviews(previews.filter((_, i) => i !== index));
 * };
 *
 * <ImageUploadInput
 *   multiple={true}
 *   label="Upload Multiple Images"
 *   placeholder="Upload Images"
 *   previews={previews}
 *   onChange={handleFilesChange}
 *   onRemove={handleRemove}
 *   maxFiles={5}
 * />
 */
