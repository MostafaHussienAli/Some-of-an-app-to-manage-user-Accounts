<?php

namespace App\Http\Resources\v1\Dash;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Manage\BaseController;

class AdminsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => BaseController::getImageUrl('Admin',$this->image),
            'token' => $this->token,
        ];
    }
}
