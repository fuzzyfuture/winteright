<?php

namespace App\Enums;

enum HideCommentsOption: int {
    case NONE = 0;
    case FRONT_PAGE_ONLY = 1;
    case ALL = 2;

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public function label(): string
    {
        return match ($this) {
            self::NONE => 'show all comments',
            self::FRONT_PAGE_ONLY => 'hide comments on the front page only',
            self::ALL => 'hide all comments'
        };
    }
}
