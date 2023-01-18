<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'images' => ImageResource::collection($this->productImages),
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at->diffForHumans(),
            'is_available' => $this->when(
                request()->route()->named(['product.allProducts', 'product.singleproduct', 'product.allProductsByItem', 'product.allFilteredProducts']),
                $this->is_available
            ),
            'translations' => $this->when(
                request()->route()->named(['product.allProducts', 'product.singleproduct', 'product.allProductsByItem']),
                $this->translations
            ),
            'managed_by' => $this->item->category->user_id,
            'item' => $this->when(
                request()->route()->named(['product.allProducts', 'product.allFilteredProducts']),
                new ItemResource($this->item)
            ),

            'sale' => $this->productInfos->sale,
            'quantity' => $this->productInfos->quantity,
            'color' => json_decode($this->productInfos->color),
            'size' => json_decode($this->productInfos->size),
            'final_price' => $this->price - ($this->price * ($this->productInfos->sale / 100))
        ];
    }
}
