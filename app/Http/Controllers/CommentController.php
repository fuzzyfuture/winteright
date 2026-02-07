<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comments\CreateCommentRequest;
use App\Services\CommentService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function postNew(CreateCommentRequest $request, int $beatmapSetId)
    {
        $userId = Auth::id();
        $validated = $request->validated();

        try {
            $this->commentService->create($userId, $beatmapSetId, $validated['content']);
        } catch (Throwable $e) {
            return back()->withErrors('error posting comment: ' . $e->getMessage());
        }

        return redirect()->route('beatmaps.show', ['set' => $beatmapSetId])
            ->with('success', 'comment posted successfully!');
    }

    public function delete(int $commentId)
    {
        $comment = $this->commentService->get($commentId);

        if (! Auth::user()->can('delete', $comment)) {
            abort(403);
        }

        try {
            $this->commentService->delete($commentId);
        } catch (Throwable $e) {
            return back()->withErrors('error deleting comment: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'comment deleted successfully!');
    }
}
