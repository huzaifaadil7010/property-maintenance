<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ResidentFilterData extends Data
{
    private const int DEFAULT_PAGE = 1;

    private const int DEFAULT_PER_PAGE = 10;

    private const array PER_PAGE_OPTIONS = [10, 20, 25, 30, 40, 50];

    public function __construct(
        public ?string $search = null,
        public ?int $page = null,
        public ?int $perPage = null,
    ) {}

    public function resolvedPage(): int
    {
        return max($this->page ?? self::DEFAULT_PAGE, self::DEFAULT_PAGE);
    }

    public function resolvedPerPage(): int
    {
        return in_array($this->perPage, self::PER_PAGE_OPTIONS, true)
            ? $this->perPage
            : self::DEFAULT_PER_PAGE;
    }
}
