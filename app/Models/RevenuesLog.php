<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenuesLog extends Model
{
    protected $table = 'revenues_log';

    public function revenueData(){
        return $this->belongsTo(Revenues::class, 'revenue_id');
    }
}
