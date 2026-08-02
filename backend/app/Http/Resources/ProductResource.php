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
            'code' => $this->code,
            'category' => new CategoryResource($this->category),
            'supplier' => $this->supplier ? new SupplierResource($this->supplier) : null,
            'quantity' => $this->quantity,
            'min_stock' => $this->min_stock,
            'expiration_date' => $this->expiration_date,
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
