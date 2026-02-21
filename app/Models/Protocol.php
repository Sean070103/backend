<?php

namespace App\Models;

use App\Services\TypesenseService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

class Protocol extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'tags',
        'author',
        'average_rating',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'average_rating' => 'decimal:2',
        ];
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'voteable');
    }

    public function updateAverageRating(): void
    {
        $this->average_rating = $this->reviews()->avg('rating') ?? 0;
        $this->save();
    }

    protected static function booted(): void
    {
        static::created(function (Protocol $protocol) {
            if (App::bound(TypesenseService::class)) {
                $svc = App::make(TypesenseService::class);
                if ($svc->enabled()) {
                    $svc->indexProtocol($protocol);
                }
            }
        });

        static::updated(function (Protocol $protocol) {
            if (App::bound(TypesenseService::class)) {
                $svc = App::make(TypesenseService::class);
                if ($svc->enabled()) {
                    $svc->indexProtocol($protocol);
                }
            }
        });

        static::deleted(function (Protocol $protocol) {
            if (App::bound(TypesenseService::class)) {
                $svc = App::make(TypesenseService::class);
                if ($svc->enabled()) {
                    $svc->deleteProtocol($protocol->id);
                }
            }
        });
    }
}