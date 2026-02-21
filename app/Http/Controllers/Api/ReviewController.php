<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Protocol;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    /**
     * Get reviews for a protocol.
     */
    public function index(string $protocolId): AnonymousResourceCollection
    {
        $protocol = Protocol::findOrFail($protocolId);
        
        $reviews = Review::where('protocol_id', $protocolId)
            ->with('user')
            ->latest()
            ->paginate(15);

        return ReviewResource::collection($reviews);
    }

    /**
     * Store a newly created review.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        // Check if user already reviewed this protocol
        $existingReview = Review::where('protocol_id', $request->protocol_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingReview) {
            // Update existing review
            $existingReview->update($request->only(['rating', 'feedback']));
            $existingReview->load('user');
            
            return new ReviewResource($existingReview);
        }

        $review = Review::create($request->validated());
        $review->load('user');

        return (new ReviewResource($review))
            ->response()
            ->setStatusCode(201);
    }
}
