<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BlacklistService
{
    /**
     * Returns true if the user with the given ID is blacklisted.
     * @param int $id The ID to check.
     * @return bool True if the user with the given ID is blacklisted, false if not.
     */
    public function isBlacklisted(int $id): bool
    {
        return DB::table('blacklist')->where('user_id', $id)->exists();
    }

    /**
     * Returns the blacklist as an array of user IDs.
     * @return array The array of user IDs in the blacklist.
     */
    public function getBlacklist(): array
    {
        return Cache::remember('blacklist', 3600, function() {
            return DB::table('blacklist')->pluck('user_id')->toArray();
        });
    }
}
