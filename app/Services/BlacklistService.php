<?php

namespace App\Services;

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
}
