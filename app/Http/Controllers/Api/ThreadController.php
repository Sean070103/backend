<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Http\Resources\ThreadResource;
use App\Models\Thread;
use App\Services\TypesenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ThreadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, TypesenseService $typesense): AnonymousResourceCollection
    {
        $query = Thread::with(['user', 'protocol']);

        // Filter by protocol
        if ($request->has('protocol_id')) {
            $query->where('protocol_id', $request->get('protocol_id'));
        }

        // Search with Typesense
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $ids = $typesense->searchThreads($searchTerm);
            
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
                // Fallback to database search
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('body', 'like', "%{$searchTerm}%");
                });
            }
        }

        // Sorting
        $sort = $request->get('sort', 'recent');
        match ($sort) {
            'recent' => $query->latest(),
            'upvoted' => $query->withSum('votes', 'value')->orderBy('votes_sum_value', 'desc'),
            default => $query->latest(),
        };

        $threads = $query->paginate(15);

        return ThreadResource::collection($threads);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreThreadRequest $request, TypesenseService $typesense): JsonResponse
    {
        $thread = Thread::create($request->validated());
        $thread->load('user', 'protocol');

        $typesense->indexThread($thread);

        return (new ThreadResource($thread))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): ThreadResource|JsonResponse
    {
        $thread = Thread::with(['user', 'protocol'])->findOrFail($id);

        return new ThreadResource($thread);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateThreadRequest $request, string $id, TypesenseService $typesense): ThreadResource|JsonResponse
    {
        $thread = Thread::findOrFail($id);
        $thread->update($request->validated());
        $thread->load('user', 'protocol');

        $typesense->indexThread($thread);

        return new ThreadResource($thread);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, TypesenseService $typesense): JsonResponse
    {
        $thread = Thread::findOrFail($id);

        $typesense->deleteThread($thread->id);
        $thread->delete();

        return response()->json(['message' => 'Thread deleted successfully'], 200);
    }
}
