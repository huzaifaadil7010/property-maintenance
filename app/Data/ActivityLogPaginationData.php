<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ActivityLogPaginationData extends Data
{
    public function __construct(
        public ?int $page = null,
        public ?int $perPage = null,
    ) {}
}
