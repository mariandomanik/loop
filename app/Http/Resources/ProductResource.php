<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $product_name
 * @property mixed $price
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class ProductResource extends JsonResource
{
    /**
     * @param $request
     * @return array{id: int, product_name: string, price: string, created_at: string, updated_at: string}
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'price' => $this->price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}