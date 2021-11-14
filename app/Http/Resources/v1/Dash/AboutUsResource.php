<?php

namespace App\Http\Resources\v1\Dash;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Manage\BaseController;

class AboutUsResource extends JsonResource
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
            'title' => $lang == 'ar' ? $this->title_ar : $this->title_en,
            'text' => $lang == 'ar' ? $this->text_ar : $this->text_en,
            'image' => BaseController::getImageUrl('AboutUs',$this->image),
        ];
    }
}
