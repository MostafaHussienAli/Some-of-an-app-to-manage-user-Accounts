<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use File,Storage;

class BaseController extends Controller
{
    //site url
    public  static function get_url(){
        return 'http://api.masrafji.com';
    }

    public  static function getImageUrl($folder,$image){
        if($image){return BaseController::get_url() . '/public/public/images/'.$folder .'/'.$image;
        }else{return BaseController::get_url() . '/public/public/images/logo.png';} 
    }

    public  static function getFileUrl($folder,$file){
        if($file){return BaseController::get_url() . '/public/public/files/'.$folder .'/'.$file;
        }else{return BaseController::get_url() . '/public/public/images/logo.png';} 
    }


    public static function saveImage($folder,$file)
    {
        $image = $file;
        $input['image'] = mt_rand(). time().'.'.$image->getClientOriginalExtension();
        $dist = public_path('/public/images/'.$folder.'/');
        $image->move($dist, $input['image']);
        return $input['image'];

    }

    public static function saveFile($name,$folder,$upFile)
    {
        $file = $upFile;
        $input['file'] = $name.'.'.$file->getClientOriginalExtension();
        $dist = public_path('/public/files/'.$folder.'/');
        $file->move($dist, $input['file']);
        return $input['file'];

    }



    /*
     * TO Delete File From server storage
     */
    public static function deleteFile($folder,$file)
    {
        $file = public_path('/public/images/'.$folder.'/'.$file);
        if(file_exists($file))
        {
            File::delete($file);
        }
    }

    /*
     * TO Delete File From server storage
     */
    public static function deleteUploadedFile($folder,$file)
    {
        $file = public_path('/public/files/'.$folder.'/'.$file);
        if(file_exists($file))
        {
            File::delete($file);
        }
    }
}
