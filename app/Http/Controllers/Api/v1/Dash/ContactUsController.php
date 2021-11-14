<?php

namespace App\Http\Controllers\Api\v1\Dash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Manage\BaseController;
use Validator,Auth,Artisan,Hash,File,Crypt;

use App\Models\ContactUs;
use App\Http\Resources\v1\Dash\ContactUsResource;

class ContactUsController extends Controller
{
    use \App\Http\Controllers\Api\ApiResponseTrait;

    /*
     * Add new ContactUs to database
     */
    public function addContactUs(Request $request)
    {
        $lang = $request->header('lang');
        $user = Auth::user();

        $validateContactUs=$this->validateContactUs($request);
        if(isset($validateContactUs)){
            return $validateContactUs;
        }

        $ContactUs=new ContactUs();
        $ContactUs->text_ar=$request->textAr;
        $ContactUs->text_en=$request->textEn;
        $ContactUs->address_ar=$request->addressAr;
        $ContactUs->address_en=$request->addressEn;
        $ContactUs->phone=$request->phone;
        $ContactUs->email=$request->email;
        $ContactUs->facebook=$request->facebook;
        $ContactUs->twitter=$request->twitter;
        $ContactUs->instagram=$request->instagram;
        $ContactUs->save();
        
        $msg=$lang=='ar' ? 'تم اضافة تواصل معنا بنجاح'  : 'Contact us added successfully';
        return $this->apiResponseData(new ContactUsResource($ContactUs),$msg);

    }

    /*
     * Edit product
    */
    public function editContactUs(Request $request)
    {
        $lang=$request->header('lang');
        $ContactUs=ContactUs::find(1);
        $check=$this->not_found($ContactUs,'تواصل معنا','Contact us',$lang);
        if(isset($check)){
            return $check;
        }

        $validateContactUs=$this->validateContactUs($request);
        if(isset($validateContactUs)){
            return $validateContactUs;
        }

        $ContactUs->text_ar=$request->textAr;
        $ContactUs->text_en=$request->textEn;
        $ContactUs->address_ar=$request->addressAr;
        $ContactUs->address_en=$request->addressEn;
        $ContactUs->phone=$request->phone;
        $ContactUs->email=$request->email;
        $ContactUs->facebook=$request->facebook;
        $ContactUs->twitter=$request->twitter;
        $ContactUs->instagram=$request->instagram;
        $ContactUs->save();
        
        $msg=$lang=='ar' ? 'تم تعديل تواصل معنا بنجاح'  : 'Contact us edited successfully';
        return $this->apiResponseData(new ContactUsResource($ContactUs),$msg);
    }


    /*
     * Show ContactUs
     */
    public function showContactUs(Request $request){
        $lang=$request->header('lang');
        $ContactUs=ContactUs::find(1);
        $check=$this->not_found($ContactUs,'تواصل معنا','Contact us',$lang);
        if(isset($check)){
            return $check;
        }
        $msg=$lang=='ar' ?'تمت العملية بنجاح' : 'success';
        return $this->apiResponseData(new ContactUsResource($ContactUs),$msg);
    }

    /*
     * Show ContactUs
     */
    public function showContactUsForEdit(Request $request){
        $lang=$request->header('lang');
        $ContactUs=ContactUs::find(1);
        $check=$this->not_found($ContactUs,'تواصل معنا','Contact us',$lang);
        if(isset($check)){
            return $check;
        }
        $msg=$lang=='ar' ?'تمت العملية بنجاح' : 'success';
        return $this->apiResponseData($ContactUs,$msg);
    }


    /*
     * @pram $request
     * @return Error message or check if cateogry is null
     */

    private function validateContactUs($request){
        $lang = $request->header('lang');
        $input = $request->all();
        $validationMessages = [
            'textAr.required' => $lang == 'ar' ?  'من فضلك ادخل عنوان النص بالعربية' :"Title in arabic is required" ,
            'textEn.required' => $lang == 'ar' ? 'من فضلك ادخل عنوان النص بالانجليزية' :"Title in english is required"  ,
            'addressAr.required' => $lang == 'ar' ?  'من فضلك ادخل العنوان بالعربية' :"Address in arabic is required" ,
            'addressEn.required' => $lang == 'ar' ? 'من فضلك ادخل العنوان بالانجليزية' :"Address in english is required"  ,
            'phone.required' => $lang == 'ar' ?  'من فضلك ادخل رقم الهاتف' :"Phone is required" ,
            'email.required' => $lang == 'ar' ? 'من فضلك ادخل البريد الالكتروني' :"Email is required"  ,
            'facebook.required' => $lang == 'ar' ?  'من فضلك حساب الفيسبوك' :"Facebook account is required" ,
            'twitter.required' => $lang == 'ar' ? 'من فضلك ادخل حساب تويتر' :"Twitter account is required"  ,
            'instagram.required' => $lang == 'ar' ?  'من فضلك ادخل حساب انستاجرام' :"Instagram account is required" ,
        ];
        $validator = Validator::make($input, [
            'textAr' => 'required',
            'textEn' => 'required',
            'addressAr' => 'required',
            'addressEn' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'facebook' => 'required',
            'twitter' => 'required',
            'instagram' => 'required',
        ], $validationMessages);
        if ($validator->fails()) {
            return $this->apiResponseMessage(0,$validator->messages()->first(), 400);
        }      
    }
}