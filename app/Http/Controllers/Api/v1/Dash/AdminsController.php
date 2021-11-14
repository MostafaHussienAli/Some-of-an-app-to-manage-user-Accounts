<?php

namespace App\Http\Controllers\Api\v1\Dash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Manage\BaseController;
use App\Http\Controllers\Manage\EmailsController;
use App\Http\Controllers\Api\NotificationMethods;
use Validator,Auth,Artisan,Hash,File,Crypt;

use App\Models\Admins;
use App\Http\Resources\v1\Dash\AdminsResource;

class AdminsController extends Controller
{
    use \App\Http\Controllers\Api\ApiResponseTrait;

    /*
     * Add new Admin to database
     */
    public function addAdmin(Request $request)
    {
        $lang = $request->header('lang');
        $user = Auth::user();

        $validateAdmin=$this->validateAdmin($request);
        if(isset($validateAdmin)){
            return $validateAdmin;
        }

        $Admin = new Admins();
        $Admin->name = $request->name;
        $Admin->email = $request->email;
        $Admin->phone = $request->phone;
        $Admin->password = Hash::make($request->password);
        if($request->image){
            $name=BaseController::saveImage('Admin',$request->file('image'));
            $Admin->image=$name;
        }
        $Admin->save();
        $Admin['token']=null;
        
        $msg=$lang=='ar' ? 'تم اضافة الادمن بنجاح'  : 'Admin added successfully';
        return $this->apiResponseData(new AdminsResource($Admin),$msg);
    }

    /*
     * Edit admin
    */
    public function editAdmin(Request $request,$AdminId)
    {
        $lang=$request->header('lang');

        $Admin=Admins::find($AdminId);
        $check=$this->not_found($Admin,'الادمن','Admin',$lang);
        if(isset($check)){
            return $check;
        }

        // $validateAdmin=$this->validateAdmin($request);
        // if(isset($validateAdmin)){
        //     return $validateAdmin;
        // }

        $input = $request->all();
        $validationMessages = [
            'name.required' => $lang == 'ar' ?  'من فضلك ادخل اسم الادمن' :"Admin name is required" ,
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
            'name' => 'required',
            'password' => 'bail|confirmed|min:6',
            'email' => 'bail|required|unique:admins,email,'.$AdminId.'|regex:/(.+)@(.+)\.(.+)/i',
            'phone' => 'bail|required|unique:admins,phone,'.$AdminId.'|numeric|min:7',
        ], $validationMessages);
        if ($validator->fails()) {
            return $this->apiResponseMessage(0,$validator->messages()->first(), 400);
        }

        $Admin->name = $request->name;
        $Admin->email = $request->email;
        $Admin->phone = $request->phone;
        if($request->password){
            $Admin->password = Hash::make($request->password);
        }
        if($request->image){
            BaseController::deleteFile('Admin',$Admin->image);
            $name=BaseController::saveImage('Admin',$request->file('image'));
            $Admin->image=$name;
        }
        $Admin->save();
        $Admin['token']=null;

        $msg=$lang=='ar' ? 'تم تعديل الادمن بنجاح'  : 'Admin edited successfully';
        return $this->apiResponseData(new AdminsResource($Admin),$msg);
    }


    /*
     * get All product for Auth shop
     */
    public function allAdmins(Request $request)
    {
        $user=Auth::user();
        $Admins=Admins::orderBy('id','desc')->get();
        foreach($Admins as $row){$row['token']=null;}
        return $this->apiResponseData(AdminsResource::collection($Admins),'success');
    }


    /*
     * Show single Admin
     */
    public function singleAdmin(Request $request,$AdminId){
        $lang=$request->header('lang');
        $Admin=Admins::find($AdminId);
        $check=$this->not_found($Admin,'الادمن','Admin',$lang);
        if(isset($check)){
            return $check;
        }
        $Admin['token']=null;
        $Admin->image = BaseController::getImageUrl('Admin',$Admin->image);
        $msg=$lang=='ar' ?'تمت العملية بنجاح' : 'success';
        return $this->apiResponseData($Admin,$msg);
    }

    /*
     * Delete Admin ..
     */

    public function deleteAdmin(Request $request,$AdminId){
        $lang=$request->header('lang');
        
        $Admin=Admins::where('id',$AdminId)->where('id','!=',1)->first();
        $check=$this->not_found($Admin,'الادمن','Admin',$lang);
        if(isset($check)){
            return $check;
        }
        
        BaseController::deleteFile('Admin',$Admin->image);
        $Admin->delete();

        $msg=$lang=='ar' ? 'تم حذف الادمن بنجاح'  : 'Admin Deleted successfully';
        return $this->apiResponseMessage(1,$msg,200);
    }


    /*
     * @pram $request
     * @return Error message or check if cateogry is null
     */

    private function validateAdmin($request){
        $lang = $request->header('lang');
        $input = $request->all();
        $validationMessages = [
            'name.required' => $lang == 'ar' ?  'من فضلك ادخل اسم الادمن' :"Admin name is required" ,
            'password.required' => $lang == 'ar' ? 'من فضلك ادخل كلمة السر' :"password is required"  ,
            'password.confirmed' => $lang == 'ar' ? 'كلمتا السر غير متطابقتان' :"The password confirmation does not match"  ,
            'password.min' => $lang == 'ar' ?  'كلمة السر يجب ان لا تقل عن 6 احرف' :"The password must be at least 6 character" ,
            'email.required' => $lang == 'ar' ? 'من فضلك ادخل البريد الالكتروني' :"email is required"  ,
            'email.unique' => $lang == 'ar' ? 'هذا البريد الالكتروني موجود لدينا بالفعل' :"email is already taken" ,
            'email.regex'=>$lang=='ar'? 'من فضلك ادخل بريد الكتروني صالح' : 'The email must be a valid email address',
            'phone.required' => $lang == 'ar' ? 'من فضك ادخل رقم الهاتف' :"phone is required"  ,
            'phone.unique' => $lang == 'ar' ? 'رقم الهاتف موجود لدينا بالفعل' :"phone is already taken" ,
            'phone.min' => $lang == 'ar' ?  'رقم الهاتف يجب ان لا يقل عن 7 ارقام' :"The phone must be at least 7 numbers" ,
            'phone.numeric' => $lang == 'ar' ?  ' الهاتف يجب ان يكون رقما' :"The phone must be a number" ,        ];
        $validator = Validator::make($input, [
            'name' => 'required',
            'password' => 'bail|required|confirmed|min:6',
            'email' => 'bail|required|unique:admins|regex:/(.+)@(.+)\.(.+)/i',
            'phone' => 'bail|required|unique:admins|numeric|min:7',
        ], $validationMessages);
        if ($validator->fails()) {
            return $this->apiResponseMessage(0,$validator->messages()->first(), 400);
        }
        
    }}
