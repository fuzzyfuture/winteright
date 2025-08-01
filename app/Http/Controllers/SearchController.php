<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected SearchService $searchService;
    protected BeatmapService $beatmapService;

    public function __construct(SearchService $searchService, BeatmapService $beatmapService)
    {
        $this->searchService = $searchService;
        $this->beatmapService = $beatmapService;
    }

    public function index(Request $request)
    {
        $artistTitle = $request->query('artist-title');
        $mapperName = $request->query('mapper-name');
        $mapperId = $request->query('mapper-id');

        $searchResults = $this->searchService->search($artistTitle, $mapperName, $mapperId);
        $searchResults->appends($request->query());

        $this->beatmapService->applyCreatorLabelsToSets($searchResults->getCollection());

        return view('search.index', compact('searchResults', 'artistTitle', 'mapperName', 'mapperId'));
    }
}
