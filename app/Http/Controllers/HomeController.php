<?php

namespace App\Http\Controllers;

use App\Services\StatsService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected StatsService $statsService;

    public function __construct(StatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    public function index()
    {
        $user = Auth::user();
        $stats = $this->statsService->getHomePageStats();

        return view('home', compact('user', 'stats'));
    }
}
