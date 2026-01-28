<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function index(Request $request)
    {
        $artistTitle = $request->query('artist_title');
        $mapperName = $request->query('mapper_name');
        $mapperId = $request->query('mapper_id');
        $page = $request->query('page') ?? 1;

        $searchResults = $this->searchService->search(Auth::user()->enabled_modes ?? 15, $artistTitle,
            $mapperName, $mapperId, $page);
        $searchResults->appends($request->query());

        return view('search.index', compact('searchResults', 'artistTitle', 'mapperName',
            'mapperId'));
    }
}
