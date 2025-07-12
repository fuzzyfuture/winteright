<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BlacklistService
{
    /**
     * Returns true if the user with the given osu! ID is blacklisted.
     * @param int $osuId The osu! ID to check.
     * @return bool True if the user with the given osu! ID is blacklisted, false if not.
     */
    public function IsBlacklisted(int $osuId): bool
    {
        return DB::table('blacklist')->where('osu_id', $osuId)->exists();
    }

    /**
     * Returns the blacklist as an array of osu! user IDs.
     * @return array The array of osu! user IDs in the blacklist.
     */
    public function GetBlacklist(): array
    {
        return Cache::remember('blacklist', 3600, function() {
            return DB::table('blacklist')->pluck('osu_id')->toArray();
        });
    }
}
