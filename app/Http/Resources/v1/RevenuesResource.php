<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class RevenuesResource extends JsonResource
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
        
        if(!$this->time){
            $this->time = 'a';
        }

        $today = now()->format('d');
        $renewingDay = \Carbon\Carbon::parse($this->renewing_date)->format('d');
        if($renewingDay < $today){
            $renewingMonth = now()->format('m')+1;
        }else{
            $renewingMonth = now()->format('m');
        }
        $renewingYear = now()->format('Y');
        $renewingDate = "$renewingYear-$renewingMonth-$renewingDay";

        return [
            'id' => $this->id,
            'name' => $lang == 'ar' ? $this->name_ar : $this->name_en,
            'renewingType' => $lang == 'ar' ? $this->renewingType->name_ar : $this->renewingType->name_en,
            'renewingDate' => $renewingDate, 
            'defaultValue' => $this->default_value,
            'totalMoney' => $this->total_money,
            'log' => $this->revenueLog($this->id,$this->time),
            'user' => $this->user,
        ];
    }
}
