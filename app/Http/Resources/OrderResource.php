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
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user' => new UserResource($this->whenLoaded('user')),
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'order_date' => $this->order_date,
            'products' => $this->whenLoaded('orderProducts', function () {
                return $this->orderProducts->map(function ($orderProduct) {
                    return [
                        'id' => $orderProduct->id,
                        'product_id' => $orderProduct->product_id,
                        'product' => $orderProduct->product ? new ProductResource($orderProduct->product) : null,
                        'quantity' => $orderProduct->quantity,
                        'unit_price' => $orderProduct->unit_price,
                        'total_price' => $orderProduct->total_price,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
