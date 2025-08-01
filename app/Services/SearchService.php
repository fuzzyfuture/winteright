<?php

namespace App\Services;

use App\Models\BeatmapSet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SearchService
{
    public function search(?string $artistTitle, ?string $mapperName, ?string $mapperId): LengthAwarePaginator
    {
        $query = BeatmapSet::with('creator');

        if (!blank($artistTitle)) {
            $query->where('artist', 'like', '%'.$artistTitle.'%')
                ->orWhere('title', 'like', '%'.$artistTitle.'%');
        }

        if (!blank($mapperName)) {
            $userService = app(UserService::class);
            $mapperId = $userService->getIdByName($mapperName);
        }

        if (!blank($mapperId)) {
            $query->where('creator_id', $mapperId);
        }

        return $query->orderBy('date_ranked', 'desc')
            ->paginate(50);
    }
}
