<?php

namespace App\Actions;

use App\Models\Property;
use Illuminate\Pagination\LengthAwarePaginator;

class GetProperties
{
    public static function handle(): LengthAwarePaginator
    {
        return Property::query()->paginate(10);
    }
}
