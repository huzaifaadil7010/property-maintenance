<?php

namespace App\Actions\Common;

use App\Data\ActivityLogData;
use App\Models\ActivityLog;
use Throwable;

use function Illuminate\Support\defer;

class LogActivity
{
    public static function handle(ActivityLogData $data): void
    {
        defer(function () use ($data): void {
            try {
                activity('organization')
                    ->performedOn($data->subject)
                    ->causedBy($data->actor)
                    ->event($data->event->value)
                    ->withProperty('title', $data->title)
                    ->tap(function (ActivityLog $activity) use ($data): void {
                        $activity->organization_id = $data->organization->id;
                    })
                    ->log($data->description);
            } catch (Throwable $exception) {
                report($exception);
            }
        });
    }
}
