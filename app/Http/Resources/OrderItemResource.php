<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'product'     => new ProductResource($this->whenLoaded('product')),
            'custom_order_item' => new CustomOrderItemResource($this->whenLoaded('custom_order_item')),
            'quantity'    => $this->quantity,
            'unit_price'  => $this->unit_price,
        ];
    }
}