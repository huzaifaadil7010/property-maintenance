<?php

namespace App\Models;

use App\Concerns\BelongsToOrganization;
use App\Enums\ActivityEventEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    use BelongsToOrganization;

    protected $appends = [
        'icon_name',
        'icon_color',
        'icon_bg_color',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'event' => ActivityEventEnum::class,
        ];
    }

    protected function iconName(): Attribute
    {
        return Attribute::get(
            fn (): string => $this->event?->getIcon() ?? 'Activity',
        );
    }

    protected function iconColor(): Attribute
    {
        return Attribute::get(
            fn (): string => $this->event?->getColor() ?? 'text-slate-700',
        );
    }

    protected function iconBgColor(): Attribute
    {
        return Attribute::get(
            fn (): string => $this->event?->getBgColor() ?? 'bg-slate-100',
        );
    }
}
