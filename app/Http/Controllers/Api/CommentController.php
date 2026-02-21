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
     * Get comments for a thread (nested)
     */
    public function index(string $threadId): AnonymousResourceCollection
    {
        $thread = Thread::findOrFail($threadId);
        
        $comments = Comment::where('thread_id', $threadId)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'replies.replies.user'])
            ->withCount('votes')
            ->get();

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created comment.
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $comment = Comment::create($request->validated());
        $comment->load('user');

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}
