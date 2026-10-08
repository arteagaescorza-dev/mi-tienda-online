<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $meta = $this->meta ?? [];
        
        // Ciberseguridad: Ocultar capas de costo MOQ al público.
        // Solo se muestran si el usuario actual tiene permisos de finanzas.
        $canViewFinancials = $request->user() && $request->user()->can('view-financials');
        
        if (!$canViewFinancials && isset($meta['moq_cost_layers'])) {
            unset($meta['moq_cost_layers']);
            unset($meta['last_moq_tier']);
            unset($meta['last_moq_cost']);
        }

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'brand' => $this->brand,
            'fabric_id' => $this->fabric_id,
            'section_name' => $this->section_name,
            'type' => $this->type,
            'price' => $this->price,
            'cost' => $this->when($canViewFinancials, $this->cost),
            'weight' => $this->weight,
            'visible' => $this->visible,
            'meta' => $meta,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'categories' => $this->whenLoaded('categories'),
        ];
    }
}

