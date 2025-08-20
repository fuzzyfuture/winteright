<?php

namespace App\Http\Controllers;

use App\Enums\UserListItemType;
use App\Services\BeatmapService;
use App\Services\UserListService;
use Illuminate\Http\Request;

class UserListController extends Controller
{
    protected UserListService $userListService;
    protected BeatmapService $beatmapService;

    public function __construct(UserListService $userListService, BeatmapService $beatmapService)
    {
        $this->userListService = $userListService;
        $this->beatmapService = $beatmapService;
    }

    public function index(Request $request)
    {
        $name = $request->query('name');
        $creatorName = $request->query('creator_name');

        $lists = $this->userListService->search($name, $creatorName);
        $lists->appends($request->query());

        return view('lists.index', compact('lists', 'name', 'creatorName'));
    }

    public function show($listId)
    {
        $list = $this->userListService->get($listId);
        $items = $this->userListService->getItems($listId);

        $beatmapItems = $items->where('item_type', UserListItemType::BEATMAP);
        $beatmapSetItems = $items->where('item_type', UserListItemType::BEATMAP_SET);

        $this->beatmapService->applyCreatorLabels($beatmapItems->map->item);
        $this->beatmapService->applyCreatorLabelsToSets($beatmapSetItems->map->item);

        return view('lists.show', compact('list', 'items'));
    }

    public function getNew()
    {
        return view('lists.new');
    }

    public function postNew()
    {

    }
}
