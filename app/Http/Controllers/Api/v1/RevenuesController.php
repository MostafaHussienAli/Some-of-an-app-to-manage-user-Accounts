<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\Manage\BaseController;
use Validator,Auth,Artisan,Hash,File,Crypt,DB;

use App\Models\Revenues;
use App\Models\RevenuesLog;
use App\Models\Savings;
use App\Models\SavingsLog;
use App\Http\Resources\v1\RevenuesResource;
use App\Http\Resources\v1\RevenuesLogResource;

class RevenuesController extends Controller
{
    use \App\Http\Controllers\Api\ApiResponseTrait;

    
    /*
     * Add new Revenue to database
     */
    public function addRevenue(Request $request)
    {
        $lang = $request->header('lang');

        $user = Auth::user();

        $validateRevenue=$this->validateRevenue($request);
        if(isset($validateRevenue)){
            return $validateRevenue;
        }

        $Revenue=new Revenues();
        $Revenue->name_ar=$request->nameAr;
        $Revenue->name_en=$request->nameEn;
        $Revenue->renewing_type_id = !$request->renewingTypeId ? 4 : $request->renewingTypeId;
        $Revenue->renewing_date = !$request->renewingDate ? date(now()) : date($request->renewingDate);
        $Revenue->default_value=$request->defaultValue;
        $Revenue->total_money = !$request->totalMoney ? 0 : $request->totalMoney;
        $Revenue->user_id=$user->id;
        $Revenue->save();

        $user->total_revenues += $request->totalMoney;
        $user->save();

        $RevenueRecord = new RevenuesLog();
        $RevenueRecord->revenue_id = $Revenue->id;
        $RevenueRecord->value = !$request->totalMoney ? 0 : $request->totalMoney;
        $RevenueRecord->type = 'addition';
        $RevenueRecord->notes = $lang == 'ar' ? 'مبلغ البداية بالحساب' : 'Account initial money';
        $RevenueRecord->save();

        $msg=$lang=='ar' ? 'تم اضافة حساب الدخل بنجاح'  : 'Revenue account added successfully';
        return $this->apiResponseData(new RevenuesResource($Revenue),$msg);

    }


    /*
     * Edit Revenue
    */
    public function editRevenue(Request $request,$RevenueId)
    {
        $lang=$request->header('lang');
        $user = Auth::user();

        $Revenue=Revenues::find($RevenueId);
        $check=$this->not_found($Revenue,'حساب الدخل','Revenue account',$lang);
        if(isset($check)){
            return $check;
        }

        $validateRevenue=$this->validateRevenue($request);
        if(isset($validateRevenue)){
            return $validateRevenue;
        }

        $Revenue->name_ar=$request->nameAr;
        $Revenue->name_en=$request->nameEn;
        $Revenue->renewing_type_id = !$request->renewingTypeId ? 4 : $request->renewingTypeId;
        $Revenue->renewing_date = !$request->renewingDate ? date(now()) : date($request->renewingDate);
        $Revenue->default_value=$request->defaultValue;
        $user->total_revenues = $user->total_revenues - $Revenue->total_money + $request->totalMoney;
        $user->save();
        $Revenue->total_money=$request->totalMoney;
        $Revenue->save();

        $RevenueRecord = RevenuesLog::where('revenue_id',$RevenueId)->first();
        $RevenueRecord->value = $request->totalMoney;
        $RevenueRecord->save();

        $msg=$lang=='ar' ? 'تم تعديل حساب الدخل بنجاح'  : 'Revenue account edited successfully';
        return $this->apiResponseData(new RevenuesResource($Revenue),$msg);
    }


    /*
     * get All product for Auth shop
     */
    public function allRevenues(Request $request)
    {
        $user=Auth::user();
        $Revenue=Revenues::where('user_id',$user->id)->orderBy('id','desc')->get();
        return $this->apiResponseData(RevenuesResource::collection($Revenue),'success');
    }


    /*
     * Show single Revenue
     */
    public function singleRevenue(Request $request,$RevenueId){
        $lang=$request->header('lang');
        $Revenue=Revenues::find($RevenueId);
        $check=$this->not_found($Revenue,'حساب الدخل','Revenue account',$lang);
        if(isset($check)){
            return $check;
        }
        $msg=$lang=='ar' ?'تمت العملية بنجاح' : 'success';
        return $this->apiResponseData($Revenue,$msg);
    }


    /*
     * Delete Revenue ..
     */

    public function deleteRevenue(Request $request,$RevenueId)
    {
        $lang=$request->header('lang');
        $user=Auth::user();

        $firstRevenue=Revenues::where('user_id',$user->id)->first();

        $Revenue=Revenues::where('id',$RevenueId)->where('id','!=',$firstRevenue->id)->first();
        $check=$this->not_found($Revenue,'حساب الدخل','Revenue account',$lang);
        if(isset($check)){
            return $check;
        }
        
        $Revenue->delete();

        $msg=$lang=='ar' ? 'تم حذف حساب الدخل بنجاح'  : 'Revenue account Deleted successfully';
        return $this->apiResponseMessage(1,$msg,200);
    }


    /*
     * Add new record to Revenue
     */
    public function addRevenueRecord(Request $request)
    {
        $lang = $request->header('lang');

        $user = Auth::user();

        $Revenue = Revenues::find($request->RevenueId);
        $check=$this->not_found($Revenue,'حساب الدخل','Revenue account',$lang);
        if(isset($check)){
            return $check;
        }

        $RevenueRecord = new RevenuesLog();
        $RevenueRecord->revenue_id = $request->RevenueId;
        $RevenueRecord->value = $request->value;
        if($request->type == 1){
            $RevenueRecord->type = 'addition';
            $Revenue->total_money += $request->value;
            $user->total_revenues += $request->value;
        }else{
            $RevenueRecord->type = 'subtraction';
            $Revenue->total_money -= $request->value;
            $user->total_revenues -= $request->value;
        }
        $RevenueRecord->notes = $request->notes;
        $RevenueRecord->save();

        $Revenue->save();
        $user->save();

        $msg=$lang=='ar' ? 'تمت العملية بنجاح'  : 'Process done successfully';
        return $this->apiResponseData(new RevenuesLogResource($RevenueRecord),$msg);
    }

    /*
     * Add new record to Revenue
     */
    public function editRevenueRecord(Request $request,$recordId)
    {
        $lang = $request->header('lang');

        $user = Auth::user();

        $RevenueRecord = RevenuesLog::find($recordId);
        $check=$this->not_found($RevenueRecord,'الريكورد','Record',$lang);
        if(isset($check)){
            return $check;
        }

        $Revenue = Revenues::find($RevenueRecord->revenue_id);

        if($RevenueRecord->type == 'addition'){
            $Revenue->total_money -= $RevenueRecord->value;
            $user->total_revenues -= $RevenueRecord->value;
        }else{
            $Revenue->total_money += $RevenueRecord->value;
            $user->total_revenues += $RevenueRecord->value;
        }

        $RevenueRecord->value = $request->value;
        if($request->type == 1){
            $RevenueRecord->type = 'addition';
            $Revenue->total_money += $request->value;
            $user->total_revenues += $request->value;
        }else{
            $RevenueRecord->type = 'subtraction';
            $Revenue->total_money -= $request->value;
            $user->total_revenues -= $request->value;
        }
        $RevenueRecord->notes = $request->notes;
        $RevenueRecord->save();

        $Revenue->save();
        $user->save();

        $msg=$lang=='ar' ? 'تم التعديل بنجاح'  : 'Editing done successfully';
        return $this->apiResponseData(new RevenuesLogResource($RevenueRecord),$msg);
    }

    /*
     * Add new record to Revenue
     */
    public function deleteRevenueRecord(Request $request,$recordId)
    {
        $lang = $request->header('lang');

        $user = Auth::user();

        $RevenueRecord = RevenuesLog::find($recordId);
        $check=$this->not_found($RevenueRecord,'الريكورد','Record',$lang);
        if(isset($check)){
            return $check;
        }
        $Revenue = Revenues::find($RevenueRecord->revenue_id);

        if($RevenueRecord->type == 'addition'){
            $Revenue->total_money -= $RevenueRecord->value;
            $user->total_revenues -= $RevenueRecord->value;
        }else{
            $Revenue->total_money += $RevenueRecord->value;
            $user->total_revenues += $RevenueRecord->value;
        }

        $Revenue->save();
        $user->save();

        $RevenueRecord->delete();

        $msg=$lang=='ar' ? 'تم حذف الريكورد بنجاح'  : 'Record deleted successfully';
        return $this->apiResponseMessage(1,$msg,200);
    }


    /*
     * Show Revenue
     */
    public function singleRevenueWithLog(Request $request,$RevenueId){
        $lang=$request->header('lang');

        $Revenue=Revenues::find($RevenueId);
        $check=$this->not_found($Revenue,'حساب الدخل','Revenue account',$lang);
        if(isset($check)){
            return $check;
        }

        $Revenue['time'] = $request->time;

        $msg=$lang=='ar' ?'تمت العملية بنجاح' : 'success';
        return $this->apiResponseData(new RevenuesResource($Revenue),$msg);
    }

    /*
     * Add from savings
     */
    public function addToRevenuefromSavings(Request $request)
    {
        $lang = $request->header('lang');

        $user = Auth::user();

        $Revenue = Revenues::find($request->revenueId);
        $check=$this->not_found($Revenue,'حساب الدخل','Revenue account',$lang);
        if(isset($check)){
            return $check;
        }

        $saving = Savings::find($request->savingId);
        $check=$this->not_found($saving,'حساب الدخل','Revenue account',$lang);
        if(isset($check)){
            return $check;
        }

        $RevenueRecord = new RevenuesLog();
        $RevenueRecord->revenue_id = $request->revenueId;
        $RevenueRecord->value = $request->value;
        $RevenueRecord->type = 'addition';
        $RevenueRecord->notes = $lang == 'ar' ? "تحويل من حساب التوفير ($saving->name_ar) الى حساب العوائد ($Revenue->name_ar)"
                                                : "transmission from ($saving->name_en) saving account to ($Revenue->name_en) revenue account";
        $RevenueRecord->save();

        $Revenue->total_money += $request->value;
        $Revenue->save();

        $SavingRecord = new SavingsLog();
        $SavingRecord->saving_id = $request->savingId;
        $SavingRecord->value = $request->value;
        $SavingRecord->type = 'subtraction';
        $SavingRecord->notes = $lang == 'ar' ? "تحويل من حساب التوفير ($saving->name_ar) الى حساب العوائد ($Revenue->name_ar)"
                                                : "transmission from ($saving->name_en) saving account to ($Revenue->name_en) revenue account";
        $SavingRecord->save();

        $saving->total_money -= $request->value;
        $saving->save();

        $user->total_revenues += $request->value;
        $user->total_savings -= $request->value;
        $user->save();

        $msg=$lang=='ar' ? 'تمت العملية بنجاح'  : 'Process done successfully';
        return $this->apiResponseMessage(1,$msg,200);
    }


    /*
     * @pram $request
     * @return Error message or check if params is null
     */

    private function validateRevenue($request){
        $lang = $request->header('lang');
        $input = $request->all();
        $validationMessages = [
            'nameAr.required' => $lang == 'ar' ?  'من فضلك ادخل اسم حساب الدخل بالعربية' :"Name in arabic is required" ,
            'nameEn.required' => $lang == 'ar' ? 'من فضلك ادخل اسم حساب الدخل بالانجليزية' :"Name in english is required"  ,
        ];
        $validator = Validator::make($input, [
            'nameAr' => 'required',
            'nameEn' => 'required',
        ], $validationMessages);
        if ($validator->fails()) {
            return $this->apiResponseMessage(0,$validator->messages()->first(), 400);
        }      
    }
}
