<?php

namespace App\Http\Controllers;

use App\Services\UserListService;

class UserListController extends Controller
{
    protected UserListService $userListService;

    public function __construct(UserListService $userListService)
    {
        $this->userListService = $userListService;
    }

    public function index()
    {
        $lists = $this->userListService->search();

        return view('lists.index', compact('lists'));
    }

    public function show($listId)
    {
        $list = $this->userListService->get($listId);

        return view('lists.show', compact('list'));
    }
}
