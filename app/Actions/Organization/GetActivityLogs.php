<?php

namespace App\Actions\Organization;

use App\Data\ActivityLogPaginationData;
use App\Models\ActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;

class GetActivityLogs
{
    public static function handle(ActivityLogPaginationData $data): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->inLog('organization')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(
                perPage: $data->getPerPage(),
                page: $data->getPage(),
            );
    }
}
