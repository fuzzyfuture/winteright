<?php

namespace App\Services;

use App\Enums\BeatmapMode;
use App\Models\BeatmapSet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Retrieves beatmap sets with the specified search parameters.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param ?string $artistTitle The artist/title to search for.
     * @param ?string $mapperName The mapper name to search for.
     * @param ?string $mapperId The mapper ID to search for.
     * @return LengthAwarePaginator The paginated search results.
     */
    public function search(int $enabledModes, ?string $artistTitle, ?string $mapperName, ?string $mapperId): LengthAwarePaginator
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);
        $query = BeatmapSet::with('creator')
            ->whereHas('beatmaps', function ($query) use ($modesArray) {
                $query->whereIn('mode', $modesArray);
            });

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
