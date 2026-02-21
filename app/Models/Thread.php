<?php

namespace App\Models;

use App\Services\TypesenseService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'title',
        'body',
        'tags',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'voteable');
    }

    protected static function booted(): void
    {
        static::created(function ($thread) {
            if (App::bound(TypesenseService::class)) {
                $svc = App::make(TypesenseService::class);
                if ($svc->enabled()) {
                    $svc->indexThread($thread);
                }
            }
        });

        static::updated(function ($thread) {
            if (App::bound(TypesenseService::class)) {
                $svc = App::make(TypesenseService::class);
                if ($svc->enabled()) {
                    $svc->indexThread($thread);
                }
            }
        });

        static::deleted(function ($thread) {
            if (App::bound(TypesenseService::class)) {
                $svc = App::make(TypesenseService::class);
                if ($svc->enabled()) {
                    $svc->deleteThread($thread->id);
                }
            }
        });
    }
}
