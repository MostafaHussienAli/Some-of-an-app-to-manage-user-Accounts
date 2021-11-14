<?php

namespace App\Http\Controllers\Manage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth,File,Mail,Crypt;

class EmailsController extends Controller
{

    /*
     * verify Email
     * send mail to custom user to  verify
     */

    public static function verify_email($user_id,$lang)
    {
        $user=User::find($user_id);
        $subject=$lang=='ar'?  'كود التفعيل': "verification code" ;
        $email=$user->email;
        $data=[];
        $data['code']=$user->code;
        $data['language']=$lang;

        $name=$user->frist_name . $user->last_name;

            $name=$user->frist_name . $user->last_name;
            Mail::send('emails.verify_email', $data, function ($mail) use ($email,$name, $subject) {
                $mail->to($email, $name);
                $mail->subject($subject);
            });

            return $email;
    }

    /*
     * @pram emal , code
     * send code to email to use it to change forget password
     */

    public static function forget_password($user,$lang)
    {
        $subject=$lang=='ar'?  'اعادة كلمة السر': "reset password" ;
        $email=$user->email;
        $data=[];
        $data['code']=$user->code;
        $data['language']=$lang;

        $name=$user->frist_name . $user->last_name;
        Mail::send('emails.forget_password', $data, function ($mail) use ($email,$name, $subject) {
            $mail->to($email, $name);
            $mail->subject($subject);
        });
        return 1;
    }

    public static function subscribe_mail($email)
    {
        $subject=get_lang()=='ar'?  'شكرا لك': "thank you" ;
        $data=[];
        $data['language']=get_lang();

        Mail::send('emails.subscribe_mail', $data, function ($mail) use ($email, $subject) {
            $mail->to($email);
            $mail->subject($subject);
        });
        return 1;
    }
}

// namespace App\Http\Controllers;
// use Illuminate\Http\Request;
// use Mail;

// use App\Http\Requests;
// use App\Http\Controllers\Controller;

// class MailController extends Controller {
//    public function basic_email() {
//       $data = array('name'=>"Virat Gandhi");
   
//       Mail::send(['text'=>'mail'], $data, function($message) {
//          $message->to('abc@gmail.com', 'Tutorials Point')->subject
//             ('Laravel Basic Testing Mail');
//          $message->from('xyz@gmail.com','Virat Gandhi');
//       });
//       echo "Basic Email Sent. Check your inbox.";
//    }
//    public function html_email() {
//       $data = array('name'=>"Virat Gandhi");
//       Mail::send('mail', $data, function($message) {
//          $message->to('abc@gmail.com', 'Tutorials Point')->subject
//             ('Laravel HTML Testing Mail');
//          $message->from('xyz@gmail.com','Virat Gandhi');
//       });
//       echo "HTML Email Sent. Check your inbox.";
//    }
//    public function attachment_email() {
//       $data = array('name'=>"Virat Gandhi");
//       Mail::send('mail', $data, function($message) {
//          $message->to('abc@gmail.com', 'Tutorials Point')->subject
//             ('Laravel Testing Mail with Attachment');
//          $message->attach('C:\laravel-master\laravel\public\uploads\image.png');
//          $message->attach('C:\laravel-master\laravel\public\uploads\test.txt');
//          $message->from('xyz@gmail.com','Virat Gandhi');
//       });
//       echo "Email Sent with attachment. Check your inbox.";
//    }
// }
