<?php

namespace App\Http\Controllers;

use App\Enums\UserListItemType;
use App\Http\Requests\UserLists\AddUserListItemRequest;
use App\Http\Requests\UserLists\CreateUserListRequest;
use App\Http\Requests\UserLists\UpdateUserListItemRequest;
use App\Http\Requests\UserLists\UpdateUserListRequest;
use App\Services\BeatmapService;
use App\Services\UserListService;
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

        return view('lists.index', ['lists' => $lists, 'name' => $name, 'creatorName' => $creatorName]);
    }

    public function show($listId)
    {
        $list = $this->userListService->getWithOwner($listId);

        if (Gate::denies('view', $list)) {
            abort(403);
        }

        return view('lists.show', [
            'list' => $list,
            'items' => $this->userListService->getItems($listId),
        ]);
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
            return back()->withErrors('error creating list: ' . $e->getMessage());
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

        return view('lists.edit', ['list' => $list]);
    }

    public function postEdit(UpdateUserListRequest $request, $listId)
    {
        $validated = $request->validated();

        try {
            $this->userListService->update($listId, $validated['name'], $validated['description'],
                $validated['is_public']);
        } catch (Throwable $e) {
            return back()->withErrors('error updating list: ' . $e->getMessage());
        }

        return redirect()->route('lists.show', ['id' => $listId])->with('success', 'list updated successfully!');
    }

    public function delete($listId)
    {
        $list = $this->userListService->get($listId);

        if (Gate::denies('delete', $list)) {
            abort(403);
        }

        try {
            $this->userListService->delete($listId);
        } catch (Throwable $e) {
            return back()->withErrors('error deleting list: ' . $e->getMessage());
        }

        return redirect()->route('lists.index')->with('success', 'list deleted successfully!');
    }

    public function getAddItem(Request $request)
    {
        $listOptions = $this->userListService->getForUser(Auth::id(), true, null)
            ->mapWithKeys(fn ($list) => [$list->id => $list->name])
            ->toArray();

        $itemTypeOptions = collect(UserListItemType::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->name])
            ->toArray();

        return view('lists.add_item', [
            'listOptions' => $listOptions,
            'itemTypeOptions' => $itemTypeOptions,
            'listId' => $request->query('list_id'),
            'itemType' => $request->query('item_type'),
            'itemId' => $request->query('item_id'),
        ]);
    }

    public function postAddItem(AddUserListItemRequest $request)
    {
        $validated = $request->validated();
        $itemType = UserListItemType::tryFrom($validated['item_type']);

        try {
            $this->userListService->createItem($validated['list_id'], $itemType, $validated['item_id'],
                $validated['description'], $validated['order']);
        } catch (Throwable $e) {
            return back()->withErrors('error adding item: ' . $e->getMessage());
        }

        return redirect()->route('lists.show', ['id' => $validated['list_id']])
            ->with('success', 'item added successfully!');
    }

    public function getEditItems($listId)
    {
        $list = $this->userListService->getWithOwner($listId);

        if (Gate::denies('update', $list)) {
            abort(403);
        }

        $items = $this->userListService->getItems($listId);

        return view('lists.edit_items', ['list' => $list, 'items' => $items]);
    }

    public function postEditItem(UpdateUserListItemRequest $request, $itemId)
    {
        $validated = $request->validated();

        try {
            $this->userListService->updateItem($itemId, $validated['description'], $validated['order']);
        } catch (Throwable $e) {
            return back()->withErrors('error updating item: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'item updated successfully!');
    }

    public function deleteItem($itemId)
    {
        $item = $this->userListService->getItem($itemId);

        if (Gate::denies('update', $item->list)) {
            abort(403);
        }

        try {
            $this->userListService->deleteItem($itemId);
        } catch (Throwable $e) {
            return back()->withErrors('error deleting item: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'item deleted successfully!');
    }

    public function favorite($listId)
    {
        $list = $this->userListService->get($listId);

        if (Gate::denies('view', $list)) {
            abort(403);
        }

        try {
            $this->userListService->favorite(Auth::id(), $listId);
        } catch (Throwable $e) {
            return back()->withErrors('error favoriting list: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'list favorited successfully!');
    }

    public function unfavorite($listId)
    {
        $list = $this->userListService->get($listId);

        if (Gate::denies('view', $list)) {
            abort(403);
        }

        try {
            $this->userListService->unfavorite(Auth::id(), $listId);
        } catch (Throwable $e) {
            return back()->withErrors('error unfavoriting list: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'list unfavorited successfully!');
    }

    public function favorites()
    {
        $lists = $this->userListService->getFavorites(Auth::id());

        return view('lists.favorites', ['lists' => $lists]);
    }
}
