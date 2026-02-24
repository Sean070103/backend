<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoteRequest;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;

class VoteController extends Controller
{
    /**
     * Store or update a vote.
     */
    public function store(StoreVoteRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Check if vote already exists
        $vote = Vote::where('user_id', $validated['user_id'])
            ->where('voteable_id', $validated['voteable_id'])
            ->where('voteable_type', $validated['voteable_type'])
            ->first();

        if ($vote) {
            // Update existing vote
            $vote->update(['value' => $validated['value']]);
        } else {
            // Create new vote
            $vote = Vote::create($validated);
        }

        // Sum all votes for this voteable (same id + type we stored) so count is always correct
        $votesCount = (int) Vote::where('voteable_id', $vote->voteable_id)
            ->where('voteable_type', $vote->voteable_type)
            ->sum('value');

        return response()->json([
            'message' => 'Vote saved successfully',
            'vote' => [
                'id' => $vote->id,
                'value' => $vote->value,
            ],
            'votes_count' => $votesCount,
        ], 200);
    }
}
