<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ActivityLogPaginationData extends Data
{
    public function __construct(
        public ?int $page = null,
        public ?int $perPage = null,
    ) {}

    public function getPage(): int
    {
        return max($this->page ?? 1, 1);
    }

    public function getPerPage(): int
    {
        return min(max($this->perPage ?? 20, 1), 50);
    }
}
