<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Manage\BaseController;

use App\Http\Resources\v1\Dash\JobsResource;
use App\Http\Resources\v1\Dash\QuotaResource;
use App\Http\Resources\v1\Dash\PromoCodeResource;
use App\Http\Resources\v1\PaymentMethodDetailsResource;

class UserResource extends JsonResource
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
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'totalRevenues' => $this->total_revenues,
            'totalSavings' => $this->total_savings,
            'totalExpenses' => $this->total_expenses,
            'totalLiabilities' => $this->total_liabilities,
            'birthdayDate'=>$this->birthday_date,
            'gender'=>$this->gender,
            'image' => BaseController::getImageUrl('users',$this->image),
            'job' => new JobsResource($this->job),
            'quota' => new QuotaResource($this->quota),
            'status' => (int)$this->status,
            'quotaEnabled' => (boolean)$this->quota_enabled,
            'isActivated' => (boolean)$this->activated,
            'notifiable' => (boolean)$this->notifiable,
            'remindable' => (boolean)$this->remindable,
            'emailable' => (boolean)$this->e_mailable,
            'salesId ' => $this->sales_id,
            'promoCode' => new PromoCodeResource($this->promoCode),
            'activePaymentMethod' => isset($this->payment_method[0]) ? new PaymentMethodDetailsResource($this->payment_method[0]) : null,
            'allPaymentMethods' => PaymentMethodDetailsResource::collection($this->all_user_payment_methods),
            'lng' => (double)$this->lng,
            'lat' => (double)$this->lat,
            'monthlyIncome' =>(double)$this->monthly_income,
            'whereDidYouKnowAboutMasrafji' => $this->question,
            'token'=>$this->token,
        ];
    }
}
