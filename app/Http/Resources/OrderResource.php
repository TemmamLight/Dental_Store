<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'customer'     => new CustomerResource($this->whenLoaded('customer')),
            'number'       => $this->number,
            'total_price'  => $this->total_price,
            'status'       => $this->status,
            'shipping_price' => $this->shipping_price,
            'notes'        => $this->notes,
            'order_type'   => $this->order_type,
            'items'        => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'   => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}