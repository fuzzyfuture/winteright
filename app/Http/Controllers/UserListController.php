<?php

namespace App\Http\Controllers;

use App\Enums\UserListItemType;
use App\Http\Requests\UserLists\AddUserListItemRequest;
use App\Http\Requests\UserLists\CreateUserListRequest;
use App\Http\Requests\UserLists\UpdateUserListRequest;
use App\Services\BeatmapService;
use App\Services\UserListService;
use App\Validators\UserListValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Throwable;

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
        $list = $this->userListService->getWithOwner($listId);

        if (Gate::denies('view', $list)) {
            abort(403);
        }

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

    public function postNew(CreateUserListRequest $request)
    {
        $userId = Auth::id();
        $validated = $request->validated();

        try {
            $list = $this->userListService->create($userId, $validated['name'], $validated['description'],
                $validated['is_public']);
        } catch (Throwable $e) {
            return back()->withErrors('error creating list: '.$e->getMessage());
        }

        return redirect()->route('lists.show', ['id' => $list->id])
            ->with('success', 'list created successfully!');
    }

    public function getEdit($listId)
    {
        $list = $this->userListService->get($listId);

        if (Gate::denies('update', $list)) {
            abort(403);
        }

        return view('lists.edit', compact('list'));
    }

    public function postEdit(Request $request, UserListValidator $validator, $listId)
    {
        if (!$validator->validate($request->input(), 'update')) {
            return back()->withInput($request->input())->withErrors($validator);
        }

        $list = $this->userListService->get($listId);

        if (Auth::user()->cannot('update', $list)) {
            return back()->withErrors('you do not have permission to edit this list.');
        }

        try {
            $data = $validator->getData();
            $list = $this->userListService->update($listId, $data['name'], $data['description'], $data['is_public']);
        } catch (Throwable $e) {
            return back()->withErrors('error updating list: '.$e->getMessage());
        }

        return redirect()->route('lists.show', ['id' => $list->id])->with('success', 'list updated successfully!');
    }

    public function delete($listId)
    {
        $list = $this->userListService->get($listId);

        if (Auth::user()->cannot('delete', $list)) {
            return back()->withErrors('you do not have permission to delete this list.');
        }

        try {
            $this->userListService->delete($listId);
        } catch (Throwable $e) {
            return back()->withErrors('error deleting list: '.$e->getMessage());
        }

        return redirect()->route('lists.index')->with('success', 'list deleted successfully!');
    }

    public function getAddItem(Request $request)
    {
        $listOptions = $this->userListService->getForUser(Auth::id(), true)
            ->mapWithKeys(fn ($list) => [$list->id => $list->name])
            ->toArray();

        $itemTypeOptions = collect(UserListItemType::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->name])
            ->toArray();

        $listId = $request->query('list_id');
        $itemType = $request->query('item_type');
        $itemId = $request->query('item_id');

        return view('lists.add_item', compact('listOptions', 'itemTypeOptions', 'listId',
            'itemType', 'itemId'));
    }

    public function postAddItem(AddUserListItemRequest $request)
    {
        $validated = $request->validated();
        $itemType = UserListItemType::tryFrom($validated['item_type']);

        try {
            $this->userListService->createItem($validated['list_id'], $itemType, $validated['item_id'],
                $validated['description'], $validated['order']);
        } catch (Throwable $e) {
            return back()->withErrors('error adding item: '.$e->getMessage());
        }

        return redirect()->route('lists.show', ['id' => $validated['list_id']])
            ->with('success', 'item added successfully!');
    }
}
