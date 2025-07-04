<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->getImageUrls();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image_urls' => $this->getImageUrls(),
            'price' => $this->price,
            'stock' => $this->stock,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    public function heading(): array
    {
        return array_keys($this->toArray(request()));
    }
}
