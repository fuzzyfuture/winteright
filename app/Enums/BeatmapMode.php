<?php

namespace App\Enums;

use JsonSerializable;

enum BeatmapMode: int implements JsonSerializable
{
    case OSU = 0;
    case TAIKO = 1;
    case FRUITS = 2;
    case MANIA = 3;

    public function jsonSerialize(): int
    {
        return $this->value;
    }

    public static function bitfieldToArray(int $bitfield): array
    {
        $modes = [];

        if ($bitfield & 1) {
            $modes[] = BeatmapMode::OSU;
        }
        if ($bitfield & 2) {
            $modes[] = BeatmapMode::TAIKO;
        }
        if ($bitfield & 4) {
            $modes[] = BeatmapMode::FRUITS;
        }
        if ($bitfield & 8) {
            $modes[] = BeatmapMode::MANIA;
        }

        return $modes;
    }

    public static function arrayToBitfield(array $modes): int
    {
        $bitfield = 0;

        foreach ($modes as $mode) {
            $bitfield |= (1 << $mode);
        }

        return $bitfield;
    }
}
