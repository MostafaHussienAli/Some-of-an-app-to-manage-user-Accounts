<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Manage\BaseController;

use Illuminate\Http\Request;

use App\Models\TimePeriods;
use App\Http\Resources\TimePeriodsResource;

class TestController extends Controller
{
    public function test(Request $request){
        $test = TimePeriods::get();
        return response()->json(['status'=>1 , 'data'=>TimePeriodsResource::collection($test) , 'message'=>'success']);
    }
}
