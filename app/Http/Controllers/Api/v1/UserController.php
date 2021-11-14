<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\Manage\BaseController;
use App\Http\Controllers\Manage\EmailsController;
use App\Http\Controllers\Api\NotificationMethods;
use Validator,Auth,Artisan,Hash,File,Crypt;
use App\User;
use App\Models\Notfication;
use App\Models\Quota;
use App\Models\PromoCode;
use App\Models\Notification;
use App\Models\PaymentMethodsDetails;
use App\Models\Revenues;
use App\Models\RevenuesLog;
use App\Http\Resources\v1\UserResource;
use App\Http\Resources\v1\NotificationResource;
use App\Http\Resources\v1\PaymentMethodDetailsResource;
use App\Http\Resources\v1\Dash\QuotaResource;
use App\Http\Resources\v1\Dash\UsersResource;

class UserController extends Controller
{
    use \App\Http\Controllers\Api\ApiResponseTrait;

    
    /*
    *
    User Registeration
    *
    */
    public function register(Request $request)
    {
        $lang = $request->header('lang');
        $input = $request->all();
        $validationMessages = [
            'firstName.required' => $lang == 'ar' ?  'من فضلك ادخل  اسمك الاول' :"first name is required" ,
            'lastName.required' => $lang == 'ar' ?  'من فضلك ادخل اسم العائلة' :"last name name is required" ,
            'password.required' => $lang == 'ar' ? 'من فضلك ادخل كلمة السر' :"password is required"  ,
            'password.confirmed' => $lang == 'ar' ? 'كلمتا السر غير متطابقتان' :"The password confirmation does not match"  ,
            'password.min' => $lang == 'ar' ?  'كلمة السر يجب ان لا تقل عن 6 احرف' :"The password must be at least 6 character" ,
            'email.required' => $lang == 'ar' ? 'من فضلك ادخل البريد الالكتروني' :"email is required"  ,
            'email.unique' => $lang == 'ar' ? 'هذا البريد الالكتروني موجود لدينا بالفعل' :"email is already taken" ,
            'email.regex'=>$lang=='ar'? 'من فضلك ادخل بريد الكتروني صالح' : 'The email must be a valid email address',
            'phone.required' => $lang == 'ar' ? 'من فضك ادخل رقم الهاتف' :"phone is required"  ,
            'phone.unique' => $lang == 'ar' ? 'رقم الهاتف موجود لدينا بالفعل' :"phone is already taken" ,
            'phone.min' => $lang == 'ar' ?  'رقم الهاتف يجب ان لا يقل عن 7 ارقام' :"The phone must be at least 7 numbers" ,
            'phone.numeric' => $lang == 'ar' ?  ' الهاتف يجب ان يكون رقما' :"The phone must be a number" ,
        ];
        $validator = Validator::make($input, [
            'firstName' => 'required',
            'lastName' => 'required',
            'password' => 'bail|required|confirmed|min:6',
            'email' => 'bail|required|unique:users|regex:/(.+)@(.+)\.(.+)/i',
            'phone' => 'bail|required|unique:users|numeric|min:7',
        ], $validationMessages);
        if ($validator->fails()) {
            return $this->apiResponseMessage(0,$validator->messages()->first(), 200);
        }

        $user = new User();
        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->email = $request->email;
        $user->birthday_date  = $request->birthdayDate;
        $user->gender  = $request->gender;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        if($request->image){
            $name=BaseController::saveImage('users',$request->file('image'));
            $user->image=$name;
        }
        $user->sales_id  = $request->salesId;
        if($request->promoCode){
            $promoCode = PromoCode::where('code',$request->promoCode)->first();
            $check=$this->not_found($promoCode,'الكود','Code',200);
            if(isset($check))
            {
                return $check;
            }
            $user->promocode_id  = $promoCode->id;
        }
        $user->job_id  = $request->jobId;
        $user->quota_id = $request->quotaId;
        $user->quota_enabled = 1;
        $user->status  = 1;
        $user->activated = 1;
        $user->question = $request->question;
        $user->lng = $request->lng;
        $user->lat = $request->lat;
        $user->monthly_income = 0;
        $code=mt_rand(999,9999);
        $user->code = $code;
        if($request->header('lang')){
            $user->lang = $request->header('lang');
        }else{
            $user->lang = 'en';
        }
        $user->notifiable=true;
        $user->remindable=true;
        $user->e_mailable=true;
        $user->sales_commission = 0;
        $user->save();
        
        $token = $user->createToken('TutsForWeb')->accessToken;
        $user['token']=$token;
        Auth::loginUsingId($user->id);
        // EmailsController::verify_email($user->id,$lang);
        $this->first_notfication($user->lang,$user->id);
        $msg=$lang == 'ar' ? ' تم التسجيل بنجاح ... تاكد من تفعيل الحساب بادخال الكود المرسل ' : 'register success .... activate your account by the sent code';
        return response()->json([ 'status'=>1,'message'=> $msg, 'data'=>new UserResource($user)]);
    }


    /**
     * @param $token
     * @param $lang
     * @param $id
     */
    private function first_notfication($lang,$id)
    {
        $notfcaion=new Notification();
        $notfcaion->title_ar='اهلا بك';
        $notfcaion->body_ar='مرحبا بك في مصرفجي';
        $notfcaion->title_en='welcome';
        $notfcaion->body_en='welcome in Masrafji';
        $notfcaion->user_id=$id;
        $notfcaion->save();
        $title=$lang=='ar' ? 'مرحبا بك ' : 'welcome';
        $desc=$lang=='ar' ? 'مرحبا بك في مصرفجي' : 'welcome in Masrafji';
    }


    /*
     * User edits his profile
     */
    public function edit_profile(Request $request)
    {
        $lang = $request->header('lang');
        $user = Auth::user();

        $check=$this->not_found($user,'العضو','user',$lang);
        if(isset($check))
        {
            return $check;
        }
        $id=Auth::user()->id;

        $input = $request->all();
        $validationMessages = [
            'firstName.required' => $lang == 'ar' ?  'من فضلك ادخل  اسمك الاول' :"first name is required" ,
            'lastName.required' => $lang == 'ar' ?  'من فضلك ادخل اسم العائلة' :"last name name is required" ,
            'email.required' => $lang == 'ar' ? 'من فضلك ادخل البريد الالكتروني' :"email is required"  ,
            'email.unique' => $lang == 'ar' ? 'هذا البريد الالكتروني موجود لدينا بالفعل' :"email is already taken" ,
            'email.regex'=>$lang=='ar'? 'من فضلك ادخل بريد الكتروني صالح' : 'The email must be a valid email address',
            'phone.required' => $lang == 'ar' ? 'من فضك ادخل رقم الهاتف' :"phone is required"  ,
            'phone.unique' => $lang == 'ar' ? 'رقم الهاتف موجود لدينا بالفعل' :"phone is already taken" ,
            'phone.min' => $lang == 'ar' ?  'رقم الهاتف يجب ان لا يقل عن 7 ارقام' :"The phone must be at least 7 numbers" ,
            'phone.numeric' => $lang == 'ar' ?  ' الهاتف يجب ان يكون رقما' :"The phone must be a number" ,
        ];
        $validator = Validator::make($input, [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'bail|required|unique:users,email,'.$id.'|regex:/(.+)@(.+)\.(.+)/i',
            'phone' => 'bail|required|unique:users,phone,'.$id.'|numeric|min:7',
        ], $validationMessages);
        if($validator->fails()) {
            return $this->apiResponseMessage(0,$validator->messages()->first(), 400);
        }

        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->email = $request->email;
        $user->birthday_date = $request->birthdayDate;
        $user->gender  = $request->gender;
        $user->phone = $request->phone;
        if($request->image){
            BaseController::deleteFile('users',$user->image);
            $name=BaseController::saveImage('users',$request->file('image'));
            $user->image=$name;
        }
        $user->job_id  = $request->jobId;
        $user->save();

        $user['token']=null;
        $msg=$lang=='ar' ?  'تمت العملية بنجاح' :'success' ;
        return $this->apiResponseData(new UserResource($user),  $msg);
    }


    /*
     * login method
     * return user opject and token
     */

    public function login(Request $request)
    {
        $lang = $request->header('lang');
        $user=User::where('phone',$request->emailOrPhone)/*->where('activated','=',1)->where('quota_enabled','=',1)*/->first();
        if(is_null($user))
        {
            $user=User::where('email',$request->emailOrPhone)/*->where('activated','=',1)->where('quota_enabled','=',1)*/->first();
            if(is_null($user))
            {
                $msg=$lang=='ar' ?  'البيانات المدخلة غير موجودة لدينا ':'user not exist' ;
                return $this->apiResponseMessage( 0,$msg, 200);
            }
        }
        $password=Hash::check($request->password,$user->password);
        if($password==true){
            // $user->is_active  = 1;
            // if($request->fireBaseToken) {
            //     $user->fire_base_token = $request->fireBaseToken;
            // }
            // $user->save();
            $token = $user->createToken('TutsForWeb')->accessToken;
            $user['token']=$token;

            Auth::loginUsingId($user->id);

            $msg=$lang=='ar' ? 'تم تسجيل الدخول بنجاح':'login success' ;
            return response()->json([ 'status'=>1,'message'=> $msg, 'data'=>new UserResource($user)]);
        }
        $msg=$lang=='ar' ?  'كلمة السر غير صحيحة' :'Password is not correct' ;
        return $this->apiResponseMessage( 0,$msg, 200);
    }

    /*
     * Show user's profile  
     */
    public function my_info(Request $request)
    {
        $lang = $request->header('lang');
        $user = Auth::user();
        $user['token'] = null;
        $msg=$lang=='ar' ?  'تمت العملية بنجاح' :'success' ;
        return $this->apiResponseData(new UserResource($user),$msg);
    }


    /*
     * Resending verification code to activate user's account
     */
    public function resend_verification_code(Request $request)
    {
        $lang=$request->header('lang');
        $user=Auth::user();
        $code=mt_rand(999,9999);
        $user->code=$code;
        $user->save();
        // EmailsController::verify_email($user->id,$lang);
        $msg = $lang=='ar' ? 'تم اعادة ارسال كود التفعيل' : 'verification code resend successfully';
        return $this->apiResponseMessage(1,$msg,200);
    }

    /*
     * check verification code
     */

    public function activate_account(Request $request)
    {
        $lang=$request->header('lang');
        $user=Auth::user();
        $code = $request->code;

        if($request->code == $user->code){
            $user->code = null;
            $user->status = 1;
            $user->activated = 1;
            $user->save();
        }else{
            $msg = $lang=='ar' ? 'الكود غير صحيح' : 'Code is not correct';
            return $this->apiResponseMessage(1,$msg,200);
        }
        $msg = $lang=='ar' ? 'تم التفعيل بنجاح' : 'Activation completed successfully';
        return $this->apiResponseMessage(1,$msg,200);
    }

    /*
     * User changes password
     */
    public function change_password(Request $request)
    {
        $lang = $request->header('lang');
        $user = Auth::user();
        $check=$this->not_found($user,'العضو','user',$lang);
        if(isset($check))
        {
            return $check;
        }

        if(!$request->newPassword){
            $msg=$lang=='ar' ? 'يجب ادخال كلمة السر الجديدة' : 'new password is required';
            return $this->apiResponseMessage(0,$msg,200);
        }
        $password=Hash::check($request->oldPassword,$user->password);
        if($password==true){
            $user->password=Hash::make($request->newPassword);
            $user->save();
            $msg=$lang=='ar' ? 'تم تغيير كلمة السر بنجاح' : 'password changed successfully';
            return $this->apiResponseMessage( 1,$msg, 200);
        }else{
            $msg=$lang=='ar' ? 'كلمة السر القديمة غير صحيحة' : 'invalid old password';
            return $this->apiResponseMessage(0,$msg, 200);
        }
    }


    /**
     * Edit profile picture
     */
    public function edit_profile_picture(Request $request)
    {
        $user=Auth::user();
        $lang=$request->header('lang');
        if($request->image){
            BaseController::deleteFile('users',$user->image);
            $name=BaseController::saveImage('users',$request->file('image'));
            $user->image=$name;
        }else{
            $msg=$lang=='ar' ? 'من فضلك ارفع الصورة' : 'please upload image';
            return $this->apiResponseMessage(0,$msg,200);
        }
        $user->save();
        $user['token']=null;
        $msg=$lang=='ar' ? 'تم رفع الصورة بنجاح' : 'image uploaded successfully';
        return $this->apiResponseData(new UserResource($user),$msg,200);

    }

    /**
     * Edit settings
     */
    public function settings(Request $request){
        $lang = $request->header('lang');
        $user=Auth::user();

        if($request->notifications){
            $user->notifiable = $request->notifications == 't' ? true : false;
        }

        if($request->reminders) {
            $user->remindable = $request->reminders == 't' ? true : false;
        }

        if($request->emails) {
            $user->e_mailable = $request->emails == 't' ? true : false;
        }

        $user->save();
        $user['token']=null;
        $msg=$lang=='ar' ?  'تم حفظ الاعدادات بنجاح' :'settings saved successfully' ;
        return response()->json([ 'status'=>1,'message'=> $msg, 'data'=>new UserResource($user)]);
    }

    
    /**
     * Change location
     */
    public function change_location(Request $request){
        $lang=$request->header('lang');
        $user=Auth::user();
        $user->lng = $request->lng;
        $user->lat = $request->lat;
        $user->save();
        return $this->apiResponseMessage(1,'success',200);
    }



    /**
     * Add payment method
     */
    public function addPaymentMethod(Request $request){
        $lang=$request->header('lang');
        $user=Auth::user();

        $PaymentMethodsDetails = new PaymentMethodsDetails;
        $PaymentMethodsDetails->payment_method_id = $request->paymentMethodId;
        $PaymentMethodsDetails->card_number = $request->cardNumber;
        $PaymentMethodsDetails->month = $request->month;
        $PaymentMethodsDetails->year = $request->year;
        $PaymentMethodsDetails->CVV2 = $request->ccv2Code;
        $PaymentMethodsDetails->user_id = $user->id;
        $PaymentMethodsDetails->notes = $request->notes;
        $PaymentMethodsDetails->save();

        if(PaymentMethodsDetails::where('user_id',$user->id)->first() != null){
            $PaymentMethodsDetails = PaymentMethodsDetails::where('id','!=',$PaymentMethodsDetails->id)->update(['active'=> 0]);
        }

        $msg=$lang=='ar' ? 'تم اضافة طريقة الدفع بنجاح' : 'Payment method added successfully';
        return $this->apiResponseMessage(0,$msg,200);
    }

    /**
     * Change payment method
     */
    public function change_payment_method(Request $request){
        $lang=$request->header('lang');
        $user=Auth::user();

        if(PaymentMethodsDetails::where('user_id',$user->id)->first() != null){
            $PaymentMethodsDetails = PaymentMethodsDetails::where('id','!=',$request->detailId)->update(['active'=> 0]);
            $changedPaymentMethods = PaymentMethodsDetails::find($request->detailId);
            $check=$this->not_found($changedPaymentMethods,'بيانات طريقة الدفع','Payment method detail',$lang);
            if(isset($check))
            {
                return $check;
            }
            $changedPaymentMethods->active = 1;
            $changedPaymentMethods->save();
        }else{
            $msg=$lang=='ar' ? 'لا توجد بيانات محفوظه لطريقة دفع' : 'There are no saved data for eny payment method';
            return $this->apiResponseMessage(0,$msg,200);
        }
        return $this->apiResponseMessage(1,'success',200);
    }


    /**
     * Delete payment method
     */
    public function deletePaymentMethod(Request $request){
        $lang=$request->header('lang');
        $user=Auth::user();

        $PaymentMethodsDetails = PaymentMethodsDetails::find($request->detailId);
        $check=$this->not_found($PaymentMethodsDetails,'بيانات طريقة الدفع','Payment method detail',$lang);
        if(isset($check))
        {
            return $check;
        }

        if($PaymentMethodsDetails->active == true){
            $PaymentMethodsDetails->delete();
            $newUsedMethodDetails = PaymentMethodsDetails::where('user_id',$user->id)->first();
            $newUsedMethodDetails->active = true;
            $newUsedMethodDetails->save();
        }else{
            $PaymentMethodsDetails->delete();
        }
        

        $msg=$lang=='ar' ? 'تم حذف طريقة الدفع بنجاح' : 'Payment method deleted successfully';
        return $this->apiResponseMessage(0,$msg,200);
    }
    

    /**
     * forgot_password
     */
    public function forget_password(Request $request){
        $lang=$request->header('lang');
        $user=User::where('email',$request->email)->first();
        $check=$this->not_found($user,'البريد الالكتروني','Email Address',$lang);
        if(isset($check)){
            return $check;
        }
        $code=mt_rand(999,9999);
        $user->code=$code;
        $user->save();
        // EmailsController::forget_password($user,$lang);
        $msg=$lang=='ar' ? 'تفحص بريدك الالكتروني' : 'check your mail';
        return $this->apiResponseMessage(1,$msg,200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function reset_password(Request $request)
    {
        $lang=$request->header('lang');
        if(!$request->code){
            $msg=$lang=='ar' ? 'من فضلك ادخل الكود' : 'code is required';
            return $this->apiResponseMessage(0,$msg,200);
        }
        $user=User::where('code',$request->code)->first();
        if(is_null($user)){
            $msg=$lang=='ar' ? 'الكود غير صحيح' : 'code is incorrect';
            return $this->apiResponseMessage(0,$msg,200);
        }
        if(!$request->password){
            $msg=$lang=='ar' ? 'من فضلك ادخل كلمة السر الجديدة' : 'new password is required';
            return $this->apiResponseMessage(0,$msg,200);
        }
        $user->password=Hash::make($request->password);
        $user->code=null;
        $user->save();
        $msg=$lang=='ar' ? 'تم تغيير كلمة السر بنجاح' : 'password changed successfully';
        return $this->apiResponseMessage(1,$msg,200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_notification(Request $request)
    {
        $user=Auth::user();
        $lang=$request->header('lang');
        $notification=Notification::where('user_id',$user->id)->get();
        $msg=$lang=='ar' ? 'تمت العملية بنجاح' : 'success';
        return $this->apiResponseData(NotificationResource::collection($notification),$msg);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function save_fire_base_token(Request $request)
    {
        $user=Auth::user();
        $welcome=true;
        if($user->fire_base_token == null){
            $welcome=false;
        }
        $user->fire_base_token=$request->fireBaseToken;
        $user->Save();
        if($welcome==false)
        {
            $this->first_notfication($user->fire_base_token,$user->lang,$user->id);
        }
        $msg=$user->lang=='ar' ? 'تم حفظ المعلومات بنجاح' : 'success';
        return $this->apiResponseMessage(1,$msg,200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $lang=$request->header('lang');
        $user=Auth::user();
        // $user->is_active=0;
        // $user->save();
        Auth::user()->tokens->each(function($token, $key) {
            $token->delete();
        });
        $msg=$lang=='ar' ? 'تم تسجيل الخروج بنجاح' : 'logout done successfully';
        return $this->apiResponseMessage(1,$msg,200);
    }

    /*
     * get All users
     */
    public function allUsers(Request $request)
    {
        $users=User::orderBy('id','desc')->get();
        return $this->apiResponseData(UsersResource::collection($users),'success');
    }

    /*
     * get single user
     */
    public function singleUser(Request $request,$userId)
    {
        $lang=$request->header('lang');

        $users=User::find($userId);
        $check=$this->not_found($users,'العميل','User',$lang);
        if(isset($check)){
            return $check;
        }

        return $this->apiResponseData(new UsersResource($users),'success');
    }

    /*
     * get user membership
     */
    public function userMembership(Request $request)
    {
        $user=Auth::user();
        $userMembership=$user->quota;
        return $this->apiResponseData(new QuotaResource($userMembership),'success');
    }

    /*
     * questions after registration
     */
    public function registerQuestions(Request $request)
    {
        $lang=$request->header('lang');
        $user=Auth::user();

        $input = $request->all();
        $validationMessages = [
            'yourMonthlyIncome.numeric' => $lang == 'ar' ?  ' دخلك الشهرى يجب ان يكون رقما' :"Your monthly income must be a number" ,
            'cashInYourPhysicalWallet.numeric' => $lang == 'ar' ?  ' المبلغ النقدي بمحفظتك يجب ان يكون رقما' :"Cash in your physical wallet must be a number" ,
        ];
        $validator = Validator::make($input, [
            'yourMonthlyIncome' => 'numeric',
            'cashInYourPhysicalWallet' => 'numeric',
        ], $validationMessages);
        if($validator->fails()) {
            return $this->apiResponseMessage(0,$validator->messages()->first(), 400);
        }

        $q1 = $request->yourMonthlyIncome;
        $user->monthly_income = $q1;
        $user->save();

        $q2 = $request->cashInYourPhysicalWallet;
        $Revenue=new Revenues();
        $Revenue->name_ar='X';
        $Revenue->name_en='X';
        $Revenue->renewing_type_id=1;
        $Revenue->renewing_date=date(\Carbon\Carbon::now());
        $Revenue->default_value=$q2;
        $Revenue->total_money = $q2;
        $Revenue->user_id=$user->id;
        $Revenue->save();

        $user->total_revenues += $q2;
        $user->save();

        $RevenueRecord = new RevenuesLog();
        $RevenueRecord->revenue_id = $Revenue->id;
        $RevenueRecord->value = $q2;
        $RevenueRecord->type = 'addition';
        $RevenueRecord->notes = $lang == 'ar' ? 'مبلغ البداية بالعوائد بعد التسجيل' : 'User revenues initial money after registration';
        $RevenueRecord->save();

        $msg=$lang=='ar' ? 'تم' : 'Done';
        return $this->apiResponseMessage(1,$msg,200);
    }


    public function sentNotifcationToCoustomUser($id)
    {
        $token=User::where('id',$id)->value('fire_base_token');
        return NotificationMethods::senNotificationToSingleUser($token,'custom','custom user',null,1,1);
    }
}
