<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table =  'notification';
    
    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }
}
