<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class MitraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $services = null;
        $drivers = null;
        if (!empty($this->mitra->services)) {
            foreach ($this->mitra->services as $val) {
                $services[] = [
                    'id' => $val->service->id ?? null,
                    'name' => $val->service->name ?? null,
                    'price' => $val->price,
                    'description' => $val->service->description ?? null,
                    'status' => $val->status,
                ];
            }
        }

        if (!empty($this->mitra->drivers)) {
            foreach ($this->mitra->drivers as $val) {
                $drivers[] = [
                    'id' => $val->auth->user_id ?? null,
                    'name' => $val->user->name ?? null,
                    'email' => $val->auth->email ?? null,
                    'photo' => $val->user->photo ?? null,
                    'phone' => $val->user->phone ?? null,
                    'address' => $val->user->address ?? null,
                    'status' => $val->auth->status ?? null,
                ];
            }
        }

        //make new key called distance and calculated from latitude and longitude of mitra add send request and only show when got latitude and longitude from request
        if (!empty($request->latitude) && !empty($request->longitude)) {
            $this->mitra->distance = $this->mitra->getDistance($request, $this->mitra->latitude, $this->mitra->longitude);
        }

        $ratingMitraStar = 0;
        //count rating start if rating is not null
        if (!empty($this->ratingMitra)) {
            //$ratingMitraStar = $this->mitra->getRating($this->ratingMitra);
            $ratingMitraStar = $this->ratingMitra->avg('rating') ?? 0;
            //convert to double, if 4 then 4.0
            $ratingMitraStar = doubleval($ratingMitraStar);

            //if rating 0 set to 0.0
            if ($ratingMitraStar == 0) {
                $ratingMitraStar = 0.0;
            }
        }

        return [
            'user_id' => $this->user_id,
            'owner' => $this->mitra->owner,
            'name' => $this->mitra->name,
            'email' => $this->email,
            'photo' => $this->mitra->photo,
            'phone' => $this->mitra->phone,
            'employees' => $this->mitra->employees,
            'address' => $this->mitra->address,
            'business_permit' => $this->mitra->business_permit,
            'is_motor' => $this->mitra->is_motor,
            'is_mobil' => $this->mitra->is_mobil,
            'latitude' => $this->mitra->latitude,
            'longitude' => $this->mitra->longitude,
            'open_hours' => $this->mitra->open_hours,
            'is_open' => $this->mitra->is_open,
            'distance' => $this->mitra->distance ?? "0 km",
            'role' => $this->role,
            'status' => $this->status,
            'services' => $services,
            'drivers' => $drivers,
            'rating' => $this->ratingMitra ?? null,
            'rating_star' => $ratingMitraStar,
        ];
    }
}
