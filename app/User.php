<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

use App\Models\Jobs;
use App\Models\Quota;
use App\Models\PromoCode;
use App\Models\Sales;
use App\Models\PaymentMethods;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function job()
    {
        return $this->belongsTo(Jobs::class,'job_id');
    }



    public function quota()
    {
        return $this->belongsTo(Quota::class,'quota_id');
    }


    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class,'promocode_id');
    }


    public function all_user_payment_methods()
    {
        return $this->belongsToMany(PaymentMethods::class, 'payment_methods_details', 'user_id', 'payment_method_id')
                    ->withPivot('id', 'card_number', 'month', 'year', 'CVV2', 'active', 'notes');
    }


    public function payment_method(){
        return $this->belongsToMany(PaymentMethods::class, 'payment_methods_details', 'user_id', 'payment_method_id')
                    ->withPivot('id', 'card_number', 'month', 'year', 'CVV2', 'active', 'notes')->where('active',true);
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class,'sales_id');
    }
     
}
