<?php

namespace App\Enums;

enum UserListItemType: string {
    case USER = 'App\\Models\\User';
    case BEATMAP = 'App\\Models\\Beatmap';
    case BEATMAP_SET = 'App\\Models\\BeatmapSet';
}
