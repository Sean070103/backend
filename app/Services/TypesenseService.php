<?php

namespace App\Services;

use App\Models\Protocol;
use App\Models\Thread;
use Typesense\Client;
use Typesense\Exceptions\ObjectNotFound;
use Typesense\Exceptions\TypesenseClientError;

class TypesenseService
{
    private ?Client $client = null;

    public function __construct()
    {
        if (! $this->enabled()) {
            return;
        }

        $this->client = new Client([
            'nodes' => [
                [
                    'host' => config('services.typesense.host'),
                    'port' => config('services.typesense.port', 443),
                    'protocol' => config('services.typesense.protocol', 'https'),
                ],
            ],
            'api_key' => config('services.typesense.api_key'),
            'connection_timeout_seconds' => 2,
        ]);
    }

    /**
     * Whether Typesense is enabled and configured (use this to test locally without Typesense).
     */
    public function enabled(): bool
    {
        return (bool) config('services.typesense.enabled')
            && config('services.typesense.host')
            && config('services.typesense.api_key');
    }

    /**
     * Initialize collections
     */
    public function initializeCollections(): void
    {
        if (! $this->client) {
            return;
        }
        $this->createProtocolsCollection();
        $this->createThreadsCollection();
    }

    /**
     * Reindex all protocols and threads from the database into Typesense.
     * Use after enabling Typesense or to repair the index.
     */
    public function reindexAll(): array
    {
        if (! $this->client) {
            return ['protocols' => 0, 'threads' => 0, 'message' => 'Typesense is not enabled.'];
        }

        $this->initializeCollections();

        $protocolCount = 0;
        Protocol::chunk(100, function ($protocols) use (&$protocolCount) {
            foreach ($protocols as $protocol) {
                $this->indexProtocol($protocol);
                $protocolCount++;
            }
        });

        $threadCount = 0;
        Thread::chunk(100, function ($threads) use (&$threadCount) {
            foreach ($threads as $thread) {
                $this->indexThread($thread);
                $threadCount++;
            }
        });

        return [
            'protocols' => $protocolCount,
            'threads' => $threadCount,
            'message' => "Reindexed {$protocolCount} protocols and {$threadCount} threads.",
        ];
    }

    /**
     * Create protocols collection
     */
    private function createProtocolsCollection(): void
    {
        try {
            $this->client->collections['protocols']->retrieve();
        } catch (ObjectNotFound $e) {
            $this->client->collections->create([
                'name' => 'protocols',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'tags', 'type' => 'string[]'],
                    ['name' => 'votes_count', 'type' => 'int32'],
                    ['name' => 'average_rating', 'type' => 'float'],
                ],
            ]);
        }
    }

    /**
     * Create threads collection
     */
    private function createThreadsCollection(): void
    {
        try {
            $this->client->collections['threads']->retrieve();
        } catch (ObjectNotFound $e) {
            $this->client->collections->create([
                'name' => 'threads',
                'fields' => [
                    ['name' => 'id', 'type' => 'string'],
                    ['name' => 'title', 'type' => 'string'],
                    ['name' => 'body', 'type' => 'string'],
                    ['name' => 'tags', 'type' => 'string[]'],
                    ['name' => 'votes_count', 'type' => 'int32'],
                ],
            ]);
        }
    }

    /**
     * Index a protocol
     */
    public function indexProtocol($protocol): void
    {
        if (! $this->client) {
            return;
        }
        try {
            $votesCount = $protocol->votes()->sum('value') ?? 0;
            
            $this->client->collections['protocols']->documents->upsert([
                'id' => (string) $protocol->id,
                'title' => $protocol->title,
                'tags' => $protocol->tags ?? [],
                'votes_count' => $votesCount,
                'average_rating' => (float) $protocol->average_rating,
            ]);
        } catch (TypesenseClientError $e) {
            \Log::error('Typesense indexing error: ' . $e->getMessage());
        }
    }

    /**
     * Index a thread
     */
    public function indexThread($thread): void
    {
        if (! $this->client) {
            return;
        }
        try {
            $votesCount = $thread->votes()->sum('value') ?? 0;
            
            $this->client->collections['threads']->documents->upsert([
                'id' => (string) $thread->id,
                'title' => $thread->title,
                'body' => $thread->body,
                'tags' => $thread->tags ?? [],
                'votes_count' => $votesCount,
            ]);
        } catch (TypesenseClientError $e) {
            \Log::error('Typesense indexing error: ' . $e->getMessage());
        }
    }

    /**
     * Delete a protocol from Typesense
     */
    public function deleteProtocol($protocolId): void
    {
        if (! $this->client) {
            return;
        }
        try {
            $this->client->collections['protocols']->documents[(string) $protocolId]->delete();
        } catch (ObjectNotFound $e) {
            // Document doesn't exist, ignore
        } catch (TypesenseClientError $e) {
            \Log::error('Typesense deletion error: ' . $e->getMessage());
        }
    }

    /**
     * Delete a thread from Typesense
     */
    public function deleteThread($threadId): void
    {
        if (! $this->client) {
            return;
        }
        try {
            $this->client->collections['threads']->documents[(string) $threadId]->delete();
        } catch (ObjectNotFound $e) {
            // Document doesn't exist, ignore
        } catch (TypesenseClientError $e) {
            \Log::error('Typesense deletion error: ' . $e->getMessage());
        }
    }

    /**
     * Search protocols
     */
    public function searchProtocols(string $query, array $params = []): array
    {
        if (! $this->client) {
            return [];
        }
        try {
            $searchParams = array_merge([
                'q' => $query,
                'query_by' => 'title,tags',
            ], $params);

            $results = $this->client->collections['protocols']->documents->search($searchParams);
            
            return array_map(function ($hit) {
                return $hit['document']['id'];
            }, $results['hits'] ?? []);
        } catch (TypesenseClientError $e) {
            \Log::error('Typesense search error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search threads
     */
    public function searchThreads(string $query, array $params = []): array
    {
        if (! $this->client) {
            return [];
        }
        try {
            $searchParams = array_merge([
                'q' => $query,
                'query_by' => 'title,body,tags',
            ], $params);

            $results = $this->client->collections['threads']->documents->search($searchParams);
            
            return array_map(function ($hit) {
                return $hit['document']['id'];
            }, $results['hits'] ?? []);
        } catch (TypesenseClientError $e) {
            \Log::error('Typesense search error: ' . $e->getMessage());
            return [];
        }
    }
}
