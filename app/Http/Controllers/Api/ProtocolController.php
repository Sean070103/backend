<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProtocolRequest;
use App\Http\Requests\UpdateProtocolRequest;
use App\Http\Resources\ProtocolResource;
use App\Models\Protocol;
use App\Services\TypesenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProtocolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, TypesenseService $typesense): AnonymousResourceCollection
    {
        $query = Protocol::query();

        // Search functionality with Typesense
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $ids = $typesense->searchProtocols($searchTerm);
            
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
                // Maintain Typesense order using CASE statement
                $orderCase = 'CASE id ';
                foreach ($ids as $index => $id) {
                    $orderCase .= "WHEN {$id} THEN {$index} ";
                }
                $orderCase .= 'END';
                $query->orderByRaw($orderCase);
            } else {
                // Fallback to database search if Typesense returns no results
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('content', 'like', "%{$searchTerm}%");
                });
            }
        }

        // Sorting
        $sort = $request->get('sort', 'recent');
        match ($sort) {
            'recent' => $query->latest(),
            'most_reviewed' => $query->withCount('reviews')->orderBy('reviews_count', 'desc'),
            'top_rated' => $query->orderBy('average_rating', 'desc'),
            'most_upvoted' => $query->withSum('votes', 'value')->orderBy('votes_sum_value', 'desc'),
            default => $query->latest(),
        };

        $protocols = $query->paginate(15);

        return ProtocolResource::collection($protocols);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProtocolRequest $request, TypesenseService $typesense): JsonResponse
    {
        $protocol = Protocol::create($request->validated());

        $typesense->indexProtocol($protocol);

        return (new ProtocolResource($protocol))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): ProtocolResource|JsonResponse
    {
        $protocol = Protocol::findOrFail($id);

        return new ProtocolResource($protocol);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProtocolRequest $request, string $id, TypesenseService $typesense): ProtocolResource|JsonResponse
    {
        $protocol = Protocol::findOrFail($id);
        $protocol->update($request->validated());

        $typesense->indexProtocol($protocol);

        return new ProtocolResource($protocol);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, TypesenseService $typesense): JsonResponse
    {
        $protocol = Protocol::findOrFail($id);
        
        $typesense->deleteProtocol($protocol->id);
        
        $protocol->delete();

        return response()->json(['message' => 'Protocol deleted successfully'], 200);
    }
}
