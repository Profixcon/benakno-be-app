<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->customer->name,
            'email' => $this->email,
            'photo' => $this->customer->photo,
            'birthdate' => $this->customer->birthdate,
            'phone' => $this->customer->phone,
            'address' => $this->customer->address,
            'role' => $this->role,
            'status' => $this->status,
            'is_google' => $this->is_google,
            'rating' => $this->ratingUser,
        ];
    }
}
