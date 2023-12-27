<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $customer_id
 * @property bool $is_paid
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property mixed $products
 */
class OrderResource extends JsonResource
{
    /**
     * @return array{created_at: string, customer_id: int, id: int, is_paid: bool, updated_at: string}
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'is_paid' => $this->is_paid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products' => ProductResource::collection($this->products),
        ];
    }
}
