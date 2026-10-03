import { useForm, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { FormEvent } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import {
    store,
    update,
} from '@/wayfinder/App/Http/Controllers/Organization/ResidentsController';

type ResidentCreatePropertyOption = {
    id: number;
    name: string;
    units_count?: number | null;
    available_units_count?: number | null;
};

type ResidentCreateUnitOption = {
    id: number;
    name: string;
    floor: string | null;
    property_id: number;
    property: {
        id: number;
        name: string;
    };
};

type ResidentCreateOptions = {
    properties: {
        data: ResidentCreatePropertyOption[];
    };
    availableUnits: {
        data: ResidentCreateUnitOption[];
    };
};

type ResidentFormData = {
    name: string;
    email: string;
    phone: string;
    property_id: number | '';
    unit_id: number | '';
};

export type EditableResident = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    property_id: number | null;
    unit_id: number | null;
    property_name: string | null;
    unit_name: string | null;
    move_in_date: string | null;
};

type ResidentDialogueProps = {
    propsToRefresh: Array<'residents' | 'residentCreateOptions'>;
    residentToEdit?: EditableResident;
    isOpen?: boolean;
    onOpenChange?: (isOpen: boolean) => void;
};

export default function ResidentDialogue({
    propsToRefresh,
    residentToEdit,
    isOpen: controlledOpen,
    onOpenChange,
}: ResidentDialogueProps) {
    const [internalOpen, setInternalOpen] = useState(false);
    const isDialogOpen = controlledOpen ?? internalOpen;
    const isEditingResident = residentToEdit !== undefined;
    const { currentOrganization, residentCreateOptions } = usePage<{
        currentOrganization: { uuid: string; name: string } | null;
        residentCreateOptions: ResidentCreateOptions;
    }>().props;
    const residentProperties = residentCreateOptions.properties.data;
    const selectableResidentUnits = useMemo(() => {
        const availableUnitOptions = residentCreateOptions.availableUnits.data;
        const hasCurrentUnit =
            residentToEdit !== undefined && Boolean(residentToEdit.unit_id);
        const currentUnitAlreadySelectable = availableUnitOptions.some(
            (unitOption) => unitOption.id === residentToEdit?.unit_id,
        );

        if (!hasCurrentUnit || currentUnitAlreadySelectable) {
            return availableUnitOptions;
        }

        return [
            ...availableUnitOptions,
            {
                id: residentToEdit.unit_id,
                name:
                    residentToEdit.unit_name ?? String(residentToEdit.unit_id),
                floor: null,
                property_id: residentToEdit.property_id ?? 0,
                property: {
                    id: residentToEdit.property_id ?? 0,
                    name: residentToEdit.property_name ?? '',
                },
            },
        ];
    }, [residentToEdit, residentCreateOptions.availableUnits.data]);
    const residentForm = useForm<ResidentFormData>({
        name: residentToEdit?.name ?? '',
        email: residentToEdit?.email ?? '',
        phone: residentToEdit?.phone ?? '',
        property_id: residentToEdit?.property_id ?? '',
        unit_id: residentToEdit?.unit_id ?? '',
    });

    const selectedResidentProperty = useMemo(() => {
        return (
            residentProperties.find(
                (propertyOption) =>
                    propertyOption.id === residentForm.data.property_id,
            ) ?? null
        );
    }, [residentForm.data.property_id, residentProperties]);

    const selectableUnitsForProperty = useMemo(() => {
        const hasSelectedProperty = selectedResidentProperty !== null;

        if (!hasSelectedProperty) {
            return [];
        }

        return selectableResidentUnits.filter(
            (unitOption) =>
                unitOption.property_id === selectedResidentProperty.id,
        );
    }, [selectableResidentUnits, selectedResidentProperty]);

    const hasSelectedProperty = selectedResidentProperty !== null;
    const hasNoSelectableUnits = selectableUnitsForProperty.length === 0;
    const selectedPropertyHasNoAvailableUnits =
        hasSelectedProperty &&
        selectedResidentProperty.available_units_count === 0 &&
        hasNoSelectableUnits;
    const propertyAvailabilityMessage = hasSelectedProperty
        ? selectedPropertyHasNoAvailableUnits
            ? 'No available units are left for this property.'
            : `${selectedResidentProperty.available_units_count ?? 0} of ${
                  selectedResidentProperty.units_count ?? 0
              } units available in this property.`
        : 'Choose a property to view the available units.';

    const hasCurrentOrganization = currentOrganization !== null;
    const hasPropertyOptions = residentProperties.length > 0;
    const hasSelectedPropertyId = residentForm.data.property_id !== '';
    const hasSelectedUnitId = residentForm.data.unit_id !== '';
    const hasNameError = Boolean(residentForm.errors.name);
    const hasEmailError = Boolean(residentForm.errors.email);
    const hasPhoneError = Boolean(residentForm.errors.phone);
    const hasPropertyError = Boolean(residentForm.errors.property_id);
    const hasUnitError = Boolean(residentForm.errors.unit_id);
    const isSavingResident = residentForm.processing;
    const canSubmitResident =
        !isSavingResident &&
        hasCurrentOrganization &&
        hasSelectedPropertyId &&
        hasSelectedUnitId;
    const dialogTitle = isEditingResident ? 'Edit resident' : 'Create resident';
    const dialogDescription = isEditingResident
        ? 'Update this resident and their current residence.'
        : 'Add a resident user and assign them to an available unit.';
    const unitPlaceholder = !hasSelectedProperty
        ? 'Select a property first'
        : hasNoSelectableUnits
          ? 'No available units'
          : 'Select a unit';
    const submitButtonLabel = isSavingResident
        ? isEditingResident
            ? 'Saving resident'
            : 'Creating resident'
        : isEditingResident
          ? 'Save resident'
          : 'Create resident';

    function submitResident(residentSubmitEvent: FormEvent<HTMLFormElement>) {
        residentSubmitEvent.preventDefault();

        if (!hasCurrentOrganization) {
            return;
        }

        residentForm.submit(
            isEditingResident
                ? update({
                      organization: currentOrganization.uuid,
                      resident: residentToEdit.id,
                  })
                : store(currentOrganization.uuid),
            {
                only: propsToRefresh,
                preserveScroll: true,
                onSuccess: () => {
                    residentForm.resetAndClearErrors();
                    handleOpenChange(false);
                },
            },
        );
    }

    function handleOpenChange(shouldOpenDialog: boolean) {
        const isExternallyControlled = onOpenChange !== undefined;

        if (isExternallyControlled) {
            onOpenChange(shouldOpenDialog);
        } else {
            setInternalOpen(shouldOpenDialog);
        }

        const isClosingDialog = !shouldOpenDialog;

        if (isClosingDialog) {
            residentForm.resetAndClearErrors();
        }
    }

    return (
        <Dialog open={isDialogOpen} onOpenChange={handleOpenChange}>
            {!isEditingResident && (
                <DialogTrigger asChild>
                    <Button
                        className="w-full sm:w-auto"
                        disabled={
                            !hasCurrentOrganization || !hasPropertyOptions
                        }
                    >
                        <Plus />
                        Create resident
                    </Button>
                </DialogTrigger>
            )}

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{dialogTitle}</DialogTitle>
                    <DialogDescription>{dialogDescription}</DialogDescription>
                </DialogHeader>

                <form onSubmit={submitResident} className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="resident-name">Name</Label>
                        <Input
                            id="resident-name"
                            value={residentForm.data.name}
                            onChange={(nameChangeEvent) =>
                                residentForm.setData(
                                    'name',
                                    nameChangeEvent.target.value,
                                )
                            }
                            autoComplete="name"
                            aria-invalid={hasNameError}
                            aria-describedby={
                                hasNameError ? 'resident-name-error' : undefined
                            }
                            required
                        />
                        <InputError
                            id="resident-name-error"
                            message={residentForm.errors.name}
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="resident-email">Email</Label>
                            <Input
                                id="resident-email"
                                type="email"
                                value={residentForm.data.email}
                                onChange={(emailChangeEvent) =>
                                    residentForm.setData(
                                        'email',
                                        emailChangeEvent.target.value,
                                    )
                                }
                                autoComplete="email"
                                aria-invalid={hasEmailError}
                                aria-describedby={
                                    hasEmailError
                                        ? 'resident-email-error'
                                        : undefined
                                }
                                required
                            />
                            <InputError
                                id="resident-email-error"
                                message={residentForm.errors.email}
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="resident-phone">Phone</Label>
                            <Input
                                id="resident-phone"
                                value={residentForm.data.phone}
                                onChange={(phoneChangeEvent) =>
                                    residentForm.setData(
                                        'phone',
                                        phoneChangeEvent.target.value,
                                    )
                                }
                                autoComplete="tel"
                                aria-invalid={hasPhoneError}
                                aria-describedby={
                                    hasPhoneError
                                        ? 'resident-phone-error'
                                        : undefined
                                }
                            />
                            <InputError
                                id="resident-phone-error"
                                message={residentForm.errors.phone}
                            />
                        </div>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="resident-property">Property</Label>
                        <Select
                            value={
                                !hasSelectedPropertyId
                                    ? ''
                                    : String(residentForm.data.property_id)
                            }
                            onValueChange={(selectedPropertyId) => {
                                residentForm.setData(
                                    'property_id',
                                    Number(selectedPropertyId),
                                );
                                residentForm.setData('unit_id', '');
                                residentForm.clearErrors('unit_id');
                            }}
                            required
                        >
                            <SelectTrigger
                                id="resident-property"
                                className="w-full"
                                aria-invalid={hasPropertyError}
                                aria-describedby={
                                    hasPropertyError
                                        ? 'resident-property-error'
                                        : undefined
                                }
                            >
                                <SelectValue placeholder="Select a property" />
                            </SelectTrigger>
                            <SelectContent>
                                {residentProperties.map((propertyOption) => (
                                    <SelectItem
                                        key={propertyOption.id}
                                        value={String(propertyOption.id)}
                                    >
                                        {propertyOption.name}{' '}
                                        <span className="text-muted-foreground">
                                            (
                                            {propertyOption.available_units_count ??
                                                0}
                                            /{propertyOption.units_count ?? 0})
                                        </span>
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError
                            id="resident-property-error"
                            message={residentForm.errors.property_id}
                        />
                        <p
                            className={
                                selectedPropertyHasNoAvailableUnits
                                    ? 'text-sm text-destructive'
                                    : 'text-sm text-muted-foreground'
                            }
                        >
                            {propertyAvailabilityMessage}
                        </p>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="resident-unit">Unit</Label>
                        <Select
                            value={
                                !hasSelectedUnitId
                                    ? ''
                                    : String(residentForm.data.unit_id)
                            }
                            onValueChange={(selectedUnitId) =>
                                residentForm.setData(
                                    'unit_id',
                                    Number(selectedUnitId),
                                )
                            }
                            required
                            disabled={
                                !hasSelectedProperty || hasNoSelectableUnits
                            }
                        >
                            <SelectTrigger
                                id="resident-unit"
                                className="w-full"
                                aria-invalid={hasUnitError}
                                aria-describedby={
                                    hasUnitError
                                        ? 'resident-unit-error'
                                        : undefined
                                }
                            >
                                <SelectValue placeholder={unitPlaceholder} />
                            </SelectTrigger>
                            <SelectContent>
                                {selectableUnitsForProperty.map(
                                    (unitOption) => (
                                        <SelectItem
                                            key={unitOption.id}
                                            value={String(unitOption.id)}
                                        >
                                            {unitOption.name}
                                            {unitOption.floor
                                                ? ` · Floor ${unitOption.floor}`
                                                : ''}
                                        </SelectItem>
                                    ),
                                )}
                            </SelectContent>
                        </Select>
                        <InputError
                            id="resident-unit-error"
                            message={residentForm.errors.unit_id}
                        />
                    </div>

                    <DialogFooter>
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="secondary"
                                disabled={isSavingResident}
                            >
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button type="submit" disabled={!canSubmitResident}>
                            {isSavingResident && <Spinner />}
                            {submitButtonLabel}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
