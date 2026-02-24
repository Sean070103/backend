<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    /**
     * Get comments for a thread.
     * Returns a flat list with parent_id so the frontend
     * can build a threaded tree on its side.
     */
    public function index(string $threadId): AnonymousResourceCollection
    {
        $thread = Thread::findOrFail($threadId);

        $comments = Comment::where('thread_id', $threadId)
            ->with('user')
            ->withCount('votes')
            ->orderBy('created_at')
            ->get();

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created comment (called from POST /api/comments with thread_id in body).
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $comment = Comment::create($request->validated());
        $comment->load('user');

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Store a comment for a thread (called from POST /api/threads/{id}/comments).
     * Frontend sends only body and optional parent_id; thread_id from URL.
     * Uses the authenticated user when available, with a safe fallback.
     */
    public function storeForThread(StoreCommentRequest $request, string $threadId): JsonResponse
    {
        $userId = $request->user()?->id ?? auth()->id() ?? $request->input('user_id', 1);

        $request->merge([
            'thread_id' => $threadId,
            'user_id' => $userId,
        ]);

        return $this->store($request);
    }
}
