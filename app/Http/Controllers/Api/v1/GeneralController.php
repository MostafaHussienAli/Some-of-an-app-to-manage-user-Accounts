<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Manage\BaseController;
use Validator,Auth,Artisan,Hash,File,Crypt,DateTime,DB;

use App\Models\Expenses;
use App\Models\Liabilities;
use App\Models\LiabilitiesLog;
use App\Models\Revenues;
use App\Models\RevenuesLog;
use App\Models\SavingsLog;
use App\Http\Resources\v1\Dash\QuotaResource;
use App\Http\Resources\v1\LiabilitiesResource;
use App\Http\Resources\v1\RevenuesResource;

class GeneralController extends Controller
{
    use \App\Http\Controllers\Api\ApiResponseTrait;

    /*
     * get home
     */
    public function home(Request $request)
    {
        $lang=$request->header('lang');
        $user=Auth::user();

        $helloMsg = $lang == 'ar' ? "اهلا $user->first_name" : "Hello $user->first_name";
        $revenues = $user->total_revenues;
        $expenses = $user->total_expenses;
        $savings = $user->total_savings;
        $liabilities = $user->total_liabilities;

        //upcomingPayments
        $paymentDates = collect(LiabilitiesResource::collection(Liabilities::where('user_id',$user->id)->where('paid',0)
        ->orderBy('payment_date','asc')->get()));
        $upcomingLiabilitiesIds = $paymentDates->SortBy('paymentDate')->take(6)->pluck('id');

        $upcomingLiabilities = Liabilities::whereIn('id',$upcomingLiabilitiesIds)->get();
        $upcomingPayments = [];
        foreach($upcomingLiabilities as $row){
            $name = $lang == 'ar' ? $row->name_ar : $row->name_en;
            $value = $row->repeating_value;
            $reason = $lang == 'ar' ? 'التزام' : 'Liability';

            $today = now()->format('d');
            $payDay = \Carbon\Carbon::parse($row->payment_date)->format('d');
            if($payDay < $today){
                $payMonth = now()->format('m')+1;
            }else{
                $payMonth = now()->format('m');
            }
            $payYear = now()->format('Y');
            $liabilityDate = "$payYear-$payMonth-$payDay";
            $date = "$liabilityDate 00:00:00";

            $upcomingPayments[]=['name'=>$name , 'reason'=>$reason , 'value'=>$value , 'date'=>$date];
        }
        $dataArr = array();
        foreach ($upcomingPayments as $key => $row)
        {
            $dataArr[$key] = $row['date'];
        }
        array_multisort($dataArr, SORT_ASC, $upcomingPayments);

        //Recent Payments
        $recentExpenses = Expenses::where('user_id',$user->id)->where('type','basic')->orderBy('created_at','desc')->take(3)->get();
        $liabilitiesIds = Liabilities::where('user_id',$user->id)->pluck('id');
        $recentLiabilities = LiabilitiesLog::whereIn('liability_id',$liabilitiesIds)->orderBy('created_at','desc')->take(3)->get();
        $recent = $recentExpenses->concat($recentLiabilities);
        $recentPayments = [];
        foreach($recent as $row){
            if(isset($row->liability_id)){
                $liabilityInfo = Liabilities::find($row->liability_id);
                $name = $lang == 'ar' ? $liabilityInfo->name_ar : $liabilityInfo->name_en;
            }else{
                $name = $lang == 'ar' ? $row->name_ar : $row->name_en;
            }
            $value = $row->value;
            if(isset($row->type)){
                $reason = $lang == 'ar' ? 'نفقات ثابتة' : 'Basic expense';
            }else{
                $reason = $lang == 'ar' ? 'التزام' : 'Liability';
            }
            $date = date($row->created_at);

            $recentPayments[]=['name'=>$name , 'reason'=>$reason , 'value'=>$value , 'date'=>$date];
        }

        //Upcoming Income
        $renewingDates = collect(RevenuesResource::collection(Revenues::where('user_id',$user->id)->where('renewing_type_id','!=',1)
        ->orderBy('renewing_date','asc')->get()));
        $upcomingRevenuesIds = $renewingDates->SortBy('renewingDate')->take(6)->pluck('id');

        $upcomingRevenues = Revenues::whereIn('id',$upcomingRevenuesIds)->get();        
        $upcomingIncome = [];
        foreach($upcomingRevenues as $row){
            $name = $lang == 'ar' ? $row->name_ar : $row->name_en;
            $value = $row->default_value;
            $reason = $lang == 'ar' ? 'عوائد ثابتة' : 'Basic revenues';

            $today = now()->format('d');
            $renewingDay = \Carbon\Carbon::parse($row->renewing_date)->format('d');
            if($renewingDay < $today){
                $renewingMonth = now()->format('m')+1;
            }else{
                $renewingMonth = now()->format('m');
            }
            $renewingYear = now()->format('Y');
            $renewingDate = "$renewingYear-$renewingMonth-$renewingDay";
            $date = "$renewingDate 00:00:00";

            $upcomingIncome[]=['name'=>$name , 'reason'=>$reason , 'value'=>$value , 'date'=>$date];
        }
        $dataArr = array();
        foreach ($upcomingIncome as $key => $row)
        {
            $dataArr[$key] = $row['date'];
        }
        array_multisort($dataArr, SORT_ASC, $upcomingIncome);

        $revenuesChart = RevenuesLog::pluck('value');
        $expensesChart = Expenses::pluck('value');
        $savingsChart = SavingsLog::pluck('value');
        $liabilitiesChart = LiabilitiesLog::pluck('value');

        $allData = ['helloMsg'=>$helloMsg,
                    'revenues'=>$revenues,
                    'revenuesChart'=>$revenuesChart,
                    'expenses'=>$expenses,
                    'expensesChart'=>$expensesChart,
                    'savings'=>$savings,
                    'savingsChart'=>$savingsChart,
                    'liabilities'=>$liabilities,
                    'liabilitiesChart'=>$liabilitiesChart,
                    'upcomingPayments'=>$upcomingPayments,
                    'recentPayments'=>$recentPayments,
                    'upcomingIncome'=>$upcomingIncome];

        return $this->apiResponseData($allData,'success');
    }


    /*
     * Show Revenue
     */
    public function taxCalculator(Request $request)
    {
        $lang=$request->header('lang');

        $value = $request->basicSalary;
        $type = $request->type;

        if($type == 'p'){

            $calcVal = $value - 750;
            
            if($value <= 750){
                $taxPer = 0;
            }elseif(1166 >= $value && $value > 750){
                $taxPer = 5;
            }elseif(1582 >= $value && $value > 1166){
                $taxPer = 10;
            }elseif(1998 >= $value && $value > 1582){
                $taxPer = 15;
            }elseif(2414 >= $value && $value > 1998){
                $taxPer = 20;
            }elseif(2414 < $value){
                $taxPer = 25;
            }
            $taxValue = $value*$taxPer/100;

        }else{

            $calcVal = $value - 1500;
            
            if($value <= 1500){
                $taxPer = 0;
            }elseif(1916 >= $value && $value > 1500){
                $taxPer = 5;
            }elseif(2332 >= $value && $value > 1916){
                $taxPer = 10;
            }elseif(2748 >= $value && $value > 2332){
                $taxPer = 15;
            }elseif(3146 >= $value && $value > 2748){
                $taxPer = 20;
            }elseif(3146 < $value){
                $taxPer = 25;
            }
            $taxValue = $value*$taxPer/100;
        
        }

        $note = $lang == 'ar' ?  "إعفاء شخصي = 9000 دينار على الدخل السنوي / 750 دينار على الدخل الشهري.
                                إعفاء عائلي = 18000 دينار على الدخل السنوي / 1500 دينار على الدخل الشهري."
                                : "Personal exemption = 9000 JOD on annual income / 750 JOD on monthly income.
                                 Family exemption = 18000 JOD on annual income / 1500 JOD on monthly income.";

        $data = ['taxValue'=>$taxValue , 'note'=>$note];

        $msg=$lang=='ar' ?'تمت العملية بنجاح' : 'success';
        return $this->apiResponseData($data,$msg);
    }
}
