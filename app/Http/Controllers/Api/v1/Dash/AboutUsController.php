<?php

namespace App\Http\Controllers\Api\v1\Dash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Manage\BaseController;
use Validator,Auth,Artisan,Hash,File,Crypt;

use App\Models\AboutUs;
use App\Http\Resources\v1\Dash\AboutUsResource;

class AboutUsController extends Controller
{
    use \App\Http\Controllers\Api\ApiResponseTrait;

    /*
     * Add new AboutUs to database
     */
    public function addAboutUs(Request $request)
    {
        $lang = $request->header('lang');
        $user = Auth::user();

        $validateAboutUs=$this->validateAboutUs($request);
        if(isset($validateAboutUs)){
            return $validateAboutUs;
        }

        $AboutUs=new AboutUs();
        $AboutUs->title_ar=$request->titleAr;
        $AboutUs->title_en=$request->titleEn;
        $AboutUs->text_ar=$request->textAr;
        $AboutUs->text_en=$request->textEn;
        if($request->image){
            $name=BaseController::saveImage('AboutUs',$request->file('image'));
            $AboutUs->image=$name;
        }
        $AboutUs->save();
        
        $msg=$lang=='ar' ? 'تم اضافة عنا بنجاح'  : 'AboutUs added successfully';
        return $this->apiResponseData(new AboutUsResource($AboutUs),$msg);

    }

    /*
     * Edit product
    */
    public function editAboutUs(Request $request,$AboutUsId)
    {
        $lang=$request->header('lang');

        $AboutUs=AboutUs::find($AboutUsId);
        $check=$this->not_found($AboutUs,'عنا','AboutUs',$lang);
        if(isset($check)){
            return $check;
        }

        $validateAboutUs=$this->validateAboutUs($request);
        if(isset($validateAboutUs)){
            return $validateAboutUs;
        }

        $AboutUs->title_ar=$request->titleAr;
        $AboutUs->title_en=$request->titleEn;
        $AboutUs->text_ar=$request->textAr;
        $AboutUs->text_en=$request->textEn;
        if($request->image){
            BaseController::deleteFile('AboutUs',$AboutUs->image);
            $name=BaseController::saveImage('AboutUs',$request->file('image'));
            $AboutUs->image=$name;
        }
        $AboutUs->save();
        
        $msg=$lang=='ar' ? 'تم تعديل عنا بنجاح'  : 'AboutUs edited successfully';
        return $this->apiResponseData(new AboutUsResource($AboutUs),$msg);
    }


    /*
     * get All product for Auth shop
     */
    public function allAboutUs(Request $request)
    {
        $user=Auth::user();
        $AboutUs=AboutUs::orderBy('id','desc')->get();
        return $this->apiResponseData(AboutUsResource::collection($AboutUs),'success');
    }


    /*
     * Show single AboutUs
     */
    public function singleAboutUs(Request $request,$AboutUsId){
        $lang=$request->header('lang');
        $AboutUs=AboutUs::find($AboutUsId);
        $check=$this->not_found($AboutUs,'عنا','AboutUs',$lang);
        if(isset($check)){
            return $check;
        }
        $AboutUs->image = BaseController::getImageUrl('aboutUs',$AboutUs->image);
        $msg=$lang=='ar' ?'تمت العملية بنجاح' : 'success';
        return $this->apiResponseData($AboutUs,$msg);
    }

    /*
     * Delete AboutUs ..
     */

    public function deleteAboutUs(Request $request,$AboutUsId){
        $lang=$request->header('lang');
        $AboutUs=AboutUs::where('id',$AboutUsId)->where('id','!=',1)->first();
        $check=$this->not_found($AboutUs,'عنا','AboutUs',$lang);
        if(isset($check)){
            return $check;
        }

        BaseController::deleteFile('AboutUs',$AboutUs->image);
        $AboutUs->delete();
        $msg=$lang=='ar' ? 'تم حذف عنا بنجاح'  : 'AboutUs Deleted successfully';
        return $this->apiResponseMessage(1,$msg,200);
    }


    /*
     * @pram $request
     * @return Error message or check if cateogry is null
     */

    private function validateAboutUs($request){
        $lang = $request->header('lang');
        $input = $request->all();
        $validationMessages = [
            'titleAr.required' => $lang == 'ar' ?  'من فضلك ادخل اسم عنا بالعربية' :"title in arabic is required" ,
            'titleEn.required' => $lang == 'ar' ? 'من فضلك ادخل عنوان اسم عنا بالانجليزية' :"title in english is required"  ,
            'textAr.required' => $lang == 'ar' ?  'من فضلك ادخل نص عنا بالعربية' :"text in arabic is required" ,
            'textEn.required' => $lang == 'ar' ? 'من فضلك ادخل عنوان نص عنا بالانجليزية' :"text in english is required"  ,
        ];
        $validator = Validator::make($input, [
            'titleAr' => 'required',
            'titleEn' => 'required',
            'textAr' => 'required',
            'textEn' => 'required',
        ], $validationMessages);
        if ($validator->fails()) {
            return $this->apiResponseMessage(0,$validator->messages()->first(), 400);
        }      
    }
}
