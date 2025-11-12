<?php

namespace App\Services;

use App\Enums\BeatmapMode;
use App\Models\BeatmapSet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    /**
     * Retrieves beatmap sets with the specified search parameters.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param ?string $artistTitle The artist/title to search for.
     * @param ?string $mapperName The mapper name to search for.
     * @param ?string $mapperId The mapper ID to search for.
     * @param ?int $pageForCache The current page. This parameter is only used for the cache key
     * (results are cached when the query is empty), it does not determine the page retrieved from the database.
     * @return LengthAwarePaginator The paginated search results.
     */
    public function search(int  $enabledModes, ?string $artistTitle, ?string $mapperName, ?string $mapperId,
                           ?int $pageForCache = 1): LengthAwarePaginator
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);
        $query = BeatmapSet::with(['creator', 'creatorName'])
            ->whereHas('beatmaps', function ($query) use ($modesArray) {
                $query->whereIn('mode', $modesArray);
            });

        if (blank($artistTitle) && blank($mapperName) && blank($mapperId)) {
            return Cache::tags('search')->remember('search_'.$enabledModes.'_'.$pageForCache, 600, function () use ($query) {
                return $query->orderBy('date_ranked', 'desc')
                    ->paginate(50);
            });
        }

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
