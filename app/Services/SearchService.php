<?php

namespace App\Services;

use App\Models\BeatmapSet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Retrieves beatmap sets with the specified search parameters.
     *
     * @param string|null $artistTitle The artist/title to search for.
     * @param string|null $mapperName The mapper name to search for.
     * @param string|null $mapperId The mapper ID to search for.
     * @return LengthAwarePaginator The paginated search results.
     */
    public function search(?string $artistTitle, ?string $mapperName, ?string $mapperId): LengthAwarePaginator
    {
        $query = BeatmapSet::with('creator');

        if (!blank($artistTitle)) {
            $query->whereRaw('CONCAT(artist, \' \', title) LIKE ?', ['%'.$artistTitle.'%']);
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
