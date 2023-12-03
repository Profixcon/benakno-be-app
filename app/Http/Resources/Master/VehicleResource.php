<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'owner' => $this->user->name ?? null,
            'nopol' => $this->nopol,
            'brand_id' => $this->brand_id,
            'brand' => $this->brand->name ?? null,
            'logo' => $this->brand->photo ?? null,
            'type_id' => $this->type_id,
            'type' => $this->type->name ?? null,
            'year' => (int) $this->year,
            'type_vehicle' => $this->type_vehicle  ?? 'Motor',
        ];
    }
}
