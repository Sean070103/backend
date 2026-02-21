<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Protocol;
use App\Models\Review;
use App\Models\Thread;
use App\Models\User;
use App\Models\Vote;
use App\Services\TypesenseService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create users
        $users = User::factory(10)->create();

        // Create 12 protocols
        $protocols = Protocol::factory(12)->create();

        // Create 10 threads
        $threads = Thread::factory(10)->create([
            'protocol_id' => fn() => $protocols->random()->id,
            'user_id' => fn() => $users->random()->id,
        ]);

        // Create 30+ comments (nested)
        $comments = collect();
        
        // Top-level comments
        $topLevelComments = Comment::factory(20)->create([
            'thread_id' => fn() => $threads->random()->id,
            'parent_id' => null,
            'user_id' => fn() => $users->random()->id,
        ]);
        
        $comments = $comments->merge($topLevelComments);
        
        // Nested replies
        $replies = Comment::factory(15)->create([
            'thread_id' => fn() => $threads->random()->id,
            'parent_id' => fn() => $topLevelComments->random()->id,
            'user_id' => fn() => $users->random()->id,
        ]);
        
        $comments = $comments->merge($replies);

        // Create 20 reviews (unique protocol_id + user_id per table constraint)
        $reviewPairs = $protocols->flatMap(fn ($p) => $users->map(fn ($u) => ['protocol_id' => $p->id, 'user_id' => $u->id]))
            ->shuffle()
            ->take(20);
        foreach ($reviewPairs as $pair) {
            Review::factory()->create([
                'protocol_id' => $pair['protocol_id'],
                'user_id' => $pair['user_id'],
            ]);
        }

        // Create 50 votes (unique user_id + voteable_id + voteable_type per table constraint)
        $voteables = collect()
            ->merge($protocols->map(fn ($p) => ['id' => $p->id, 'type' => Protocol::class]))
            ->merge($threads->map(fn ($t) => ['id' => $t->id, 'type' => Thread::class]))
            ->merge($comments->map(fn ($c) => ['id' => $c->id, 'type' => Comment::class]));

        $voteTriples = $users->flatMap(fn ($u) => $voteables->map(fn ($v) => ['user_id' => $u->id, 'voteable_id' => $v['id'], 'voteable_type' => $v['type']]))
            ->shuffle()
            ->take(50);
        foreach ($voteTriples as $triple) {
            Vote::factory()->create([
                'user_id' => $triple['user_id'],
                'voteable_id' => $triple['voteable_id'],
                'voteable_type' => $triple['voteable_type'],
            ]);
        }

        // Update average_rating for all protocols
        foreach ($protocols as $protocol) {
            $protocol->updateAverageRating();
        }

        // Initialize Typesense collections and index data
        if (App::bound(TypesenseService::class)) {
            $typesense = App::make(TypesenseService::class);
            $typesense->initializeCollections();
            
            foreach ($protocols as $protocol) {
                $typesense->indexProtocol($protocol);
            }
            
            foreach ($threads as $thread) {
                $typesense->indexThread($thread);
            }
        }
    }
}
