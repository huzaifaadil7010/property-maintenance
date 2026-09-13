<?php

namespace App\Actions\Organization;

use App\Data\ActivityLogPaginationData;
use App\Models\ActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;

class GetActivityLogs
{
    public static function handle(ActivityLogPaginationData $data): LengthAwarePaginator
    {
        $page = max($data->page ?? 1, 1);
        $perPage = min(max($data->perPage ?? 20, 1), 50);

        return ActivityLog::query()
            ->inLog('organization')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(
                perPage: $perPage,
                page: $page,
            );
    }
}
