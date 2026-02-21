<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TypesenseService;
use Illuminate\Http\JsonResponse;

class ReindexController extends Controller
{
    /**
     * Reindex all protocols and threads into Typesense.
     */
    public function __invoke(TypesenseService $typesense): JsonResponse
    {
        if (! $typesense->enabled()) {
            return response()->json([
                'message' => 'Typesense is not enabled.',
                'protocols' => 0,
                'threads' => 0,
            ], 422);
        }

        $result = $typesense->reindexAll();

        return response()->json($result, 200);
    }
}
