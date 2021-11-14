<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Http\Resources\v1\RevenuesLogResource;
use DB;

class Revenues extends Model
{
    protected $table = 'revenues';

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function renewingType()
    {
        return $this->belongsTo(TimePeriods::class,'renewing_type_id');
    }

    public function revenueLog($revenueId,$time)
    {
        if($time == 'd'){
            $revenueDates = RevenuesLog::where('revenue_id',$revenueId)->whereDate('created_at', now())->select(DB::raw('DATE(created_at) as date'))->distinct()->get()->pluck('date');
        }elseif($time == 'm'){
            $revenueDates = RevenuesLog::where('revenue_id',$revenueId)->whereMonth('created_at', now())->whereYear('created_at', now())->select(DB::raw('DATE(created_at) as date'))->distinct()->get()->pluck('date');
        }elseif($time == 'y'){
            $revenueDates = RevenuesLog::where('revenue_id',$revenueId)->whereYear('created_at', now())->select(DB::raw('DATE(created_at) as date'))->distinct()->get()->pluck('date');
        }elseif($time == 'a'){
            $revenueDates = RevenuesLog::where('revenue_id',$revenueId)->select(DB::raw('DATE(created_at) as date'))->distinct()->get()->pluck('date');
        }
        
        $data = [];
        foreach($revenueDates as $row){
            $date = \Carbon\Carbon::createFromFormat('Y-m-d',$row);
            $dateLog = RevenuesLog::where('revenue_id',$revenueId)->whereDate('created_at',$date)->orderBy('created_at','DESC')->get();
            $data[] = ['date' => $row , 'records' => RevenuesLogResource::collection($dateLog)];
        }

        $dateArr = array();
        foreach ($data as $key => $row)
        {
            $dateArr[$key] = $row['date'];
        }
        array_multisort($dateArr, SORT_DESC, $data);

        return $data;
    }
}
