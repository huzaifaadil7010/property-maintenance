<?php

namespace App\Actions\Organization;

use App\Data\TechnicianFilterData;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;

class GetTechnicians
{
    public static function handle(TechnicianFilterData $filters): LengthAwarePaginator
    {
        return User::query()
            ->select(['id', 'name', 'email', 'phone'])
            ->technician()
            ->with([
                'technicianProfiles' => fn (HasMany $query): HasMany => $query
                    ->select(['id', 'user_id', 'specialty', 'phone', 'is_available']),
            ])
            ->latest('id')
            ->when($filters->search, fn ($query, $search) => self::filterBySearch($query, $search))
            ->paginate(
                perPage: $filters->resolvedPerPage(),
                page: $filters->resolvedPage(),
            );
    }

    private static function filterBySearch(Builder $query, string $search): void
    {
        $query->where(
            fn (Builder $query): Builder => $query
                ->whereAny(['name', 'email', 'phone'], 'like', "%{$search}%")
                ->orWhereHas(
                    'technicianProfiles',
                    fn (Builder $query): Builder => $query->whereAny(
                        ['specialty', 'phone'],
                        'like',
                        "%{$search}%",
                    ),
                ),
        );
    }
}
