<?php

namespace App\Services;

use App\Enums\HideRatingsOption;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserService
{
    /**
     * Checks if a user with the given ID exists. Returns true if the user exists.
     * @param int $id The user ID to check.
     * @return bool True if the user exists.
     */
    public function exists(int $id): bool
    {
        return User::whereId($id)->exists();
    }

    /**
     * Retrieves the user with the given ID.
     *
     * @param int $id The user's ID.
     * @return User The user.
     */
    public function get(int $id): User
    {
        return User::whereId($id)->firstOrFail();
    }

    /**
     * Retrieves a user's name from their ID. Uses the beatmap creator names table as a fallback, if the user is not
     * a winteright user.
     * @param int $id The user's ID.
     * @return string The user's name.
     */
    public function getName(int $id): string
    {
        $user = User::whereId($id)->first();

        if ($user) {
            return $user->name;
        }

        return DB::table('beatmap_creator_names')->where('id', $id)->value('name');
    }

    /**
     * Retrieves a user's ID from their name. Uses the beatmap creator names table as a fallback, if the user is not
     * a winteright user.
     * @param string $name The user's name.
     * @return int The user's ID, or -1 if the user is not a winteright user or in the beatmap creator names table.
     */
    public function getIdByName(string $name): int
    {
        $user = User::whereName($name)->first();

        if ($user) {
            return $user->id;
        }

        $creatorId = DB::table('beatmap_creator_names')->where('name', $name)->value('id');

        if ($creatorId) {
            return $creatorId;
        }

        return -1;
    }

    /**
     * Retrieves a user's ID by name. Does not use the beatmap creator names table as a fallback - only retrieves the
     * ID if the user is a winteright user.
     * @param string $name The user's name.
     * @return int The user's ID if they're a winteright user - otherwise -1.
     */
    public function getIdByNameUsersOnly(string $name): int
    {
        $user = User::whereName($name)->first();

        if ($user) {
            return $user->id;
        }

        return -1;
    }

    /**
     * Updates a user's enabled beatmap modes.
     *
     * @param int $userId The user's ID.
     * @param bool $osu True to enable osu.
     * @param bool $taiko True to enable taiko.
     * @param bool $fruits True to enable fruits.
     * @param bool $mania True to enable mania.
     * @return void
     * @throws Throwable
     */
    public function updateEnabledModes(int $userId, bool $osu, bool $taiko, bool $fruits, bool $mania): void
    {
        $enabledModes = 0;

        if ($osu) $enabledModes |= (1 << 0);     // 0001 or +1
        if ($taiko) $enabledModes |= (1 << 1);   // 0010 or +2
        if ($fruits) $enabledModes |= (1 << 2);  // 0100 or +4
        if ($mania) $enabledModes |= (1 << 3);   // 1000 or +8

        DB::transaction(function () use ($userId, $enabledModes) {
            User::where('id', $userId)->update(['enabled_modes' => $enabledModes]);
        });
    }

    /**
     * Update's a user's hide ratings setting.
     *
     * @param int $userId The user's ID.
     * @param HideRatingsOption $option The selected option.
     * @throws Throwable
     */
    public function updateHideRatings(int $userId, HideRatingsOption $option): void
    {
        DB::transaction(function () use ($userId, $option) {
           User::where('id', $userId)->update(['hide_ratings' => $option->value]);
        });
    }
}
