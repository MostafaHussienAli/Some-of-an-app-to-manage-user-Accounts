<?php

namespace App\Http\Controllers\Api\v1\Dash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Manage\BaseController;
use Validator,Auth,Artisan,Hash,File,Crypt;

use App\Models\CustomerSupport;
use App\Http\Resources\v1\Dash\CustomerSupportResource;

class CustomerSupportController extends Controller
{
    use \App\Http\Controllers\Api\ApiResponseTrait;

    /*
     * Add new CustomerSupportRequest to database
     */
    public function addCustomerSupportRequest(Request $request)
    {
        $validateCustomerSupportRequest=$this->validateCustomerSupportRequest($request);
        if(isset($validateCustomerSupportRequest)){
            return $validateCustomerSupportRequest;
        }

        $CustomerSupportRequest=new CustomerSupport();
        $CustomerSupportRequest->e_mail=$request->email;
        $CustomerSupportRequest->phone=$request->phone;
        $CustomerSupportRequest->problem_id=$request->problemId;
        $CustomerSupportRequest->notes=$request->notes;
        $CustomerSupportRequest->save();
        $lang = $request->header('lang');
        $msg=$lang=='ar' ? 'تم اضافة طلب الدعم بنجاح'  : 'Customer support request added successfully';
        return $this->apiResponseData(new CustomerSupportResource($CustomerSupportRequest),$msg);
    }


    /*
     * get All product for Auth shop
     */
    public function allCustomerSupportRequests(Request $request)
    {
        $CustomerSupportRequests=CustomerSupport::orderBy('created_at','desc')->get();
        return $this->apiResponseData(CustomerSupportResource::collection($CustomerSupportRequests),'success');
    }

    /*
     * Show single CustomerSupportRequest
     */
    public function singleCustomerSupportRequest(Request $request,$CustomerSupportRequestId){
        $lang=$request->header('lang');
        $CustomerSupportRequest=CustomerSupport::find($CustomerSupportRequestId);
        $check=$this->not_found($CustomerSupportRequest,'طلب الدعم','Customer support request',$lang);
        if(isset($check)){
            return $check;
        }
        $msg=$lang=='ar' ?'تمت العملية بنجاح' : 'success';
        return $this->apiResponseData($CustomerSupportRequest,$msg);
    }

    /*
     * Delete CustomerSupportRequest ..
     */
    public function deleteCustomerSupportRequest(Request $request,$CustomerSupportRequestId){
        $lang=$request->header('lang');
        $CustomerSupportRequest=CustomerSupport::find($CustomerSupportRequestId);
        $check=$this->not_found($CustomerSupportRequest,'طلب الدعم','Customer support request',$lang);
        if(isset($check)){
            return $check;
        }
        $CustomerSupportRequest->delete();
        $msg=$lang=='ar' ? 'تم حذف طلب الدعم بنجاح'  : 'Customer support request Deleted successfully';
        return $this->apiResponseMessage(1,$msg,200);
    }


    /*
     * @pram $request
     * @return Error message or check if cateogry is null
     */

    private function validateCustomerSupportRequest($request){
        $lang = $request->header('lang');
        $input = $request->all();
        $validationMessages = [
            'email.required' => $lang == 'ar' ?  'من فضلك ادخل البريد الالكتروني' :"Email is required" ,
            'email.regex'=>$lang=='ar'? 'من فضلك ادخل بريد الكتروني صالح' : 'The email must be a valid email address',
            'phone.required' => $lang == 'ar' ? 'من فضلك ادخل عنوان رقم الهاتف' :"Phone is required"  ,
            'phone.min' => $lang == 'ar' ?  'رقم الهاتف يجب ان لا يقل عن 7 ارقام' :"The phone must be at least 7 numbers" ,
            'phone.numeric' => $lang == 'ar' ?  ' الهاتف يجب ان يكون رقما' :"The phone must be a number" ,
            'problemId.required' => $lang == 'ar' ? 'من فضلك ادخل عنوان المشكلة' :"Problem is required"  ,
        ];
        $validator = Validator::make($input, [
            'email' => 'required|regex:/(.+)@(.+)\.(.+)/i',
            'phone' => 'required|numeric|min:7',
            'problemId' => 'required',
        ], $validationMessages);
        if ($validator->fails()) {
            return $this->apiResponseMessage(0,$validator->messages()->first(), 400);
        }      
    }}
