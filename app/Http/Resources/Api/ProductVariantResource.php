<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray($request)
    {
        $canViewFinancials = $request->user() && $request->user()->can('view-financials');

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'attributes' => $this->attributes,
            'price' => $this->price,
            'cost' => $this->when($canViewFinancials, $this->cost),
            'visible' => $this->visible,
            'option_values' => $this->whenLoaded('optionValues'),
        ];
    }
}

