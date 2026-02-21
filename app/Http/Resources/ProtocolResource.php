<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProtocolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'tags' => $this->tags ?? [],
            'author' => $this->author,
            'average_rating' => (float) $this->average_rating,
            'votes_count' => $this->votes()->sum('value') ?? 0,
            'threads_count' => $this->threads()->count(),
            'reviews_count' => $this->reviews()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
