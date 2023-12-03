<?php

namespace App\Http\Resources\Chats;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomResource extends JsonResource
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
            'chat_name' => $this->chat_name ,
            'service_id' => $this->service_id,
            'sender_id' => $this->sender_id,
            'sender' => $this->sender,
            'participants' => $this->participants,
            'status' => $this->status,
            'service' => $this->service,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
