<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use Exception;
use Illuminate\Support\Facades\Auth;

class PlayedController extends Controller
{
    protected BeatmapService $beatmapService;

    public function __construct(BeatmapService $beatmapService)
    {
        $this->beatmapService = $beatmapService;
    }

    public function playedRecent()
    {
        try {
            $beatmaps = $this->beatmapService->getRecentlyPlayedForUser(Auth::id());
        } catch (Exception $e) {
            return back()->withErrors('error while retrieving recently played from the osu! API: ' . $e->getMessage());
        }

        $ratingOptions = ['' => 'unrated', 0 => '0.0', 1 => '0.5', 2 => '1.0', 3 => '1.5', 4 => '2.0', 5 => '2.5',
            6 => '3.0', 7 => '3.5', 8 => '4.0', 9 => '4.5', 10 => '5.0'];

        return view('played.recent', compact('beatmaps', 'ratingOptions'));
    }
}
