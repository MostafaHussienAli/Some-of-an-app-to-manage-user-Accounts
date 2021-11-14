<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class RevenuesLogResource extends JsonResource
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

        if($this->type == 'addition'){
            $type = $lang == 'ar' ? 'اضافة' : $this->type;
        }else{
            $type = $lang == 'ar' ? 'خصم' : $this->type;
        }

        return [
            'id' => $this->id,
            'savingAccountName' => $lang == 'ar' ? $this->revenueData->name_ar : $this->revenueData->name_en,
            'processType' => $type,
            'value' => $this->value,
            'date' => date($this->created_at),
            'notes' => $this->notes,
        ];
    }
}
