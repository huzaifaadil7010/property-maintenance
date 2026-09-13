<?php

namespace App\Data;

use App\Enums\ActivityEventEnum;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

class ActivityLogData extends Data
{
    public function __construct(
        public ActivityEventEnum $event,
        public string $title,
        public string $description,
        public Model $subject,
        public User $actor,
        public Organization $organization,
    ) {}
}
