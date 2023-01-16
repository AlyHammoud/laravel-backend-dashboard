<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

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
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'mobile' => $this->mobile,
            'created_at' => $this->created_at,
            'role' => $this->role->name,
            'verified' => $this->email_verified_at ? true : false,
            'token' => $this->when($this->token !== null, $this->token),
            'image' => $this->image ? URL::to('storage/users_image/' . $this->image)  : null

        ];
    }
}
