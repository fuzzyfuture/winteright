<?php

return [
    'urls' => [
        'user' => [
            'avatar' => 'https://a.ppy.sh/{id}',
            'profile' => 'https://osu.ppy.sh/users/{id}',
        ],
        'beatmap' => [
            'info' => 'https://osu.ppy.sh/beatmapsets/{set_id}#{mode}/{beatmap_id}',
            'set_info' => 'https://osu.ppy.sh/beatmapsets/{set_id}',
            'cover' => 'https://assets.ppy.sh/beatmaps/{set_id}/covers/cover.jpg',
            'preview' => 'https://b.ppy.sh/preview/{set_id}.mp3',
            'direct' => 'osu://b/{beatmap_id}',
        ],
        'api' => [
            'base' => 'https://osu.ppy.sh/api/v2',
            'endpoints' => [
                'user' => [
                    'me' => '/me',
                    'info' => '/users/{id}',
                    'scores' => '/users/{id}/scores/{type}',
                    'beatmap_sets' => '/users/{id}/beatmapsets/{type}',
                ],
                'beatmap_set' => [
                    'search' => '/beatmapsets/search',
                    'info' => '/beatmapsets/{set_id}',
                ],
            ],
            'oauth' => [
                'base' => 'https://osu.ppy.sh/api/oauth',
                'authorize' => 'https://osu.ppy.sh/oauth/authorize',
                'token' => 'https://osu.ppy.sh/oauth/token',
                'redirect' => ENV('OSU_REDIRECT_URI', 'http://127.0.0.1:8000/oauth/osu/callback'),
            ],
        ],
    ],
];
