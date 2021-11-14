<?php

namespace App\Http\Resources\v1\Dash;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactUsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $lang = $request->header('lang');

        return [
            'id' => $this->id,
            'text' => $lang == 'ar' ? $this->text_ar : $this->text_en,
            'address' => $lang == 'ar' ? $this->address_ar : $this->address_en,
            'phone' => $this->phone,
            'email' => $this->email,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
        ];
    }
}
