<?php

namespace App\Helpers;

class OsuUrl
{
    /**
     * Retrieves the osu! URL for a user's avatar.
     *
     * @param  int  $userId  The user's ID.
     * @return string The osu! URL to the user's avatar.
     */
    public static function userAvatar(int $userId): string
    {
        return str_replace('{id}', $userId, config('osu.urls.user.avatar'));
    }

    /**
     * Retrieves the osu! URl for a user's profile.
     *
     * @param  int  $userId  The user's ID.
     * @return string The osu! URL to the user's profile.
     */
    public static function userProfile(int $userId): string
    {
        return str_replace('{id}', $userId, config('osu.urls.user.profile'));
    }

    /**
     * Retrieves the osu! URL for a beatmap set's info page, with a specific beatmap selected.
     *
     * @param  int  $setId  The beatmap set's ID.
     * @param  string  $mode  The beatmap's mode.
     * @param  int  $beatmapId  The beatmap's ID.
     * @return string The osu! URL for a beatmap's info page.
     */
    public static function beatmapInfo(int $setId, string $mode, int $beatmapId): string
    {
        return str_replace(
            ['{set_id}', '{mode}', '{beatmap_id}'],
            [$setId, $mode, $beatmapId],
            config('osu.urls.beatmap.info')
        );
    }

    /**
     * Retrieves the osu! URL for a beatmap set's info page.
     *
     * @param  int  $setId  The beatmap set's ID.
     * @return string The osu! URL for the beatmap set's info page.
     */
    public static function beatmapSetInfo(int $setId): string
    {
        return str_replace('{set_id}', $setId, config('osu.urls.beatmap.set_info'));
    }

    /**
     * Retrieves the osu! URL for a beatmap set's cover (banner / cropped bg) image.
     *
     * @param  int  $setId  The beatmap set's ID.
     * @return string The osu! URL for the beatmap set's cover image.
     */
    public static function beatmapCover(int $setId): string
    {
        return str_replace('{set_id}', $setId, config('osu.urls.beatmap.cover'));
    }

    /**
     * Retrieves the osu! URL for a beatmap set's audio preview.
     *
     * @param  int  $setId  The beatmap set's ID.
     * @return string The osu! URL for the beatmap set's audio preview.
     */
    public static function beatmapPreview(int $setId): string
    {
        return str_replace('{set_id}', $setId, config('osu.urls.beatmap.preview'));
    }

    /**
     * Retrieves the osu!direct URL for a beatmap.
     *
     * @param  int  $beatmapId  The beatmap's ID.
     * @return string The beatmap's osu!direct URL.
     */
    public static function beatmapDirect(int $beatmapId): string
    {
        return str_replace('{beatmap_id}', $beatmapId, config('osu.urls.beatmap.direct'));
    }

    /**
     * Retrieves the osu! API OAuth authorize URL.
     *
     * @return string The osu! API OAuth authorize URL.
     */
    public static function apiOauthAuthorize(): string
    {
        return config('osu.urls.api.oauth.authorize');
    }

    /**
     * Retrieves the osu! API OAuth token URL.
     *
     * @return string The osu! API OAuth token URL.
     */
    public static function apiOauthToken(): string
    {
        return config('osu.urls.api.oauth.token');
    }

    /**
     * Retrieves the osu! API OAuth redirect URL.
     *
     * @return string The osu! API OAuth redirect URL.
     */
    public static function apiOauthRedirect(): string
    {
        return config('osu.urls.api.oauth.redirect');
    }

    /**
     * Retrieves the osu! API "me" URL.
     *
     * @return string The osu! API "me" URL.
     */
    public static function apiUserMe(): string
    {
        return config('osu.urls.api.base') . config('osu.urls.api.endpoints.user.me');
    }

    /**
     * Retrieves the osu! API user info URL for the specified user.
     *
     * @param  int  $id  The user's ID.
     * @return string The osu! API user info URL.
     */
    public static function apiUserInfo(int $id): string
    {
        return config('osu.urls.api.base')
            . str_replace('{id}', $id, config('osu.urls.api.endpoints.user.info'));
    }

    /**
     * Retrieves the osu! API user scores URL for the specified user and score type.
     *
     * @param  int  $id  The user's ID.
     * @param  string  $type  The score type.
     * @return string The osu! API user scores URL.
     */
    public static function apiUserScores(int $id, string $type): string
    {
        return config('osu.urls.api.base')
            . str_replace(
                ['{id}', '{type}'],
                [$id, $type],
                config('osu.urls.api.endpoints.user.scores')
            );
    }

    /**
     * Retrieves the osu! API user beatmap sets URL for the specified user and beatmap set type.
     *
     * @param  int  $id  The user's ID.
     * @param  string  $type  The beatmap set type.
     * @return string The osu! API user beatmap sets URL.
     */
    public static function apiUserBeatmapSets(int $id, string $type): string
    {
        return config('osu.urls.api.base')
            . str_replace(
                ['{id}', '{type}'],
                [$id, $type],
                config('osu.urls.api.endpoints.user.beatmap_sets')
            );
    }

    /**
     * Retrieves the osu! API beatmap set search URL.
     *
     * @return string The osu! API beatmap set search URL.
     */
    public static function apiBeatmapSetSearch(): string
    {
        return config('osu.urls.api.base') . config('osu.urls.api.endpoints.beatmap_set.search');
    }

    /**
     * Retrieves the osu! API beatmap set info URL for the specified beatmap set.
     *
     * @param  int  $setId  The beatmap set's ID.
     * @return string The osu! API beatmap set info URL.
     */
    public static function apiBeatmapSetInfo(int $setId): string
    {
        return config('osu.urls.api.base')
            . str_replace('{set_id}', $setId, config('osu.urls.api.endpoints.beatmap_set.info'));
    }
}
