<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'user_id' => $this['user_id'],
            'name' => $this['name'],
            'email' => $this['email'],
            'photo' => $this['photo'],
            'role' => $this['role'],
            'status' => $this['status'],
            'is_google' => $this['is_google'] ?? 0,
        ];
    }
}
