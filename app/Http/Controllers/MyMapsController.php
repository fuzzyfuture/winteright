<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Throwable;

class MyMapsController extends Controller
{
    protected BeatmapService $beatmapService;

    public function __construct(BeatmapService $beatmapService)
    {
        $this->beatmapService = $beatmapService;
    }

    public function update()
    {
        Cache::tags('api_'.Auth::id())->flush();

        return redirect()->back()->with('success', 'updated successfully!');
    }

    public function recentlyPlayed()
    {
        try {
            $beatmaps = $this->beatmapService->getRecentlyPlayedForUser(Auth::id());
        } catch (AuthenticationException) {
            return redirect(route('auth.login'));
        } catch (Throwable $e) {
            return back()->withErrors('error while retrieving recently played from the osu! API: ' . $e->getMessage());
        }

        $ratingOptions = ['' => 'unrated', 0 => '0.0', 1 => '0.5', 2 => '1.0', 3 => '1.5', 4 => '2.0', 5 => '2.5',
            6 => '3.0', 7 => '3.5', 8 => '4.0', 9 => '4.5', 10 => '5.0'];
        $current = 'recent';

        return view('my_maps.recent', compact('current', 'beatmaps', 'ratingOptions'));
    }

    public function favorites(Request $request)
    {
        $page = $request->get('page', 1);

        try {
            $sets = $this->beatmapService->getFavoritesForUserPaginated(Auth::id(), $page);
        } catch (AuthenticationException) {
            return redirect(route('auth.login'));
        } catch (Throwable $e) {
            return back()->withErrors('error while retrieving favorites from the osu! API: ' . $e->getMessage());
        }

        $current = 'favorites';

        return view('my_maps.favorites', compact('current', 'sets'));
    }
}
