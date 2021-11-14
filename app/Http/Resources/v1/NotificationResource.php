<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Manage\BaseController;

class NotificationResource extends JsonResource
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
            'title' => $lang == 'ar' ? $this->title_ar : $this->title_en ,
            'body' => $lang == 'ar' ? $this->body_ar : $this->body_en ,
            'image' => BaseController::getImageUrl('Notification',$this->image),
        ];    
    }
}
