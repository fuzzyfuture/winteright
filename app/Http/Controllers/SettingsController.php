<?php

namespace App\Http\Controllers;

use App\Enums\HideRatingsOption;
use App\Http\Requests\Settings\UpdateEnabledModesRequest;
use App\Http\Requests\Settings\UpdateHideRatingsRequest;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class SettingsController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function show()
    {
        $hideRatingsOptions = HideRatingsOption::options();

        return view('settings.show', compact('hideRatingsOptions'));
    }

    public function enabledModes(UpdateEnabledModesRequest $request)
    {
        try {
            $this->userService->updateEnabledModes(Auth::id(), $request->boolean('osu'),
                $request->boolean('taiko'), $request->boolean('fruits'), $request->boolean('mania'));
        } catch (Throwable $e) {
            return back()->withErrors('error updating modes: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'enabled modes updated successfully!');
    }

    public function hideRatings(UpdateHideRatingsRequest $request)
    {
        try {
            $this->userService->updateHideRatings(
                Auth::id(),
                HideRatingsOption::from($request->get('hide_ratings'))
            );
        } catch (Throwable $e) {
            return back()->withErrors('error updating hide ratings setting: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'hide ratings setting updated successfully!');
    }
}
