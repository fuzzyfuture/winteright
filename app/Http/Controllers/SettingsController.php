<?php

namespace App\Http\Controllers;

use App\Enums\HideCommentsOption;
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
        return view('settings.show', [
            'hideRatingsOptions' => HideRatingsOption::options(),
            'hideCommentsOptions' => HideCommentsOption::options(),
        ]);
    }

    public function enabledModes(UpdateEnabledModesRequest $request)
    {
        try {
            $this->userService->updateEnabledModes(Auth::id(), $request->boolean('osu'),
                $request->boolean('taiko'), $request->boolean('fruits'), $request->boolean('mania'));
        } catch (Throwable $e) {
            return back()->withErrors('error updating modes: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'enabled modes updated successfully!');
    }

    public function privacy(UpdateHideRatingsRequest $request)
    {
        try {
            $this->userService->updatePrivacySettings(
                Auth::id(),
                HideRatingsOption::from($request->get('hide_ratings')),
                HideCommentsOption::from($request->get('hide_comments')),
            );
        } catch (Throwable $e) {
            return back()->withErrors('error updating privacy settings: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'privacy settings updated successfully!');
    }
}
