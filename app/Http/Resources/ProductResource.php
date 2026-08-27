<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image ?: 'https://via.placeholder.com/300?text=' . urlencode($this->name),
            'price' => $this->price,
            'old_price' => $this->old_price,
            'discount_percent' => $this->discount_percent,
            'in_stock' => $this->stock > 0,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'url' => route('product.show', $this->slug),
        ];
    }
}
