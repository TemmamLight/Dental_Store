<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'favoriteable_id'   => $this->favoriteable_id,
            'favoriteable_type' => $this->favoriteable_type,
            'favoriteable'      => new \App\Http\Resources\CategoryResource($this->whenLoaded('favoriteable')),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}