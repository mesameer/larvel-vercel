<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\SiteApi;
use Mail;
class SiteController extends Controller
{
    public function index(Request $request) { 
        $api = new SiteApi();
        return $siteApiResponse = $api->generateApiResponse($request->all());
    }

    public function contactForm(Request $request) { 
        $contactName=$request->contactName;
        $email=$request->email;
        $phone=$request->phone;
        $subject ="Contact form submission ".$request->domain;
        $To="alan@logicalcommerce.com";
        $message = '<p><strong>User information detail is :-</strong></p><p><strong>Name : </strong>'.$contactName.'</p><p><strong>Email : </strong>'.$email.'</p><p><strong>Phone number : </strong>'.$phone.'</p>';
        
        // Mail::send(['text'=>'mail'], $data, function($message) {
        //     $message->to('abc@gmail.com', 'Tutorials Point')->subject
        //        ('Laravel Basic Testing Mail');
        //     $message->from('xyz@gmail.com','Virat Gandhi');
        //  });
        //  echo "Basic Email Sent. Check your inbox.";
        $headers =  'MIME-Version: 1.0' . "\r\n"; 
        $headers .= 'From: '.$contactName .'<'.$email.'>' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 
        $d=mail($To, $subject, $message, $headers);
        if($d) {
            echo "success";
        } else {
            echo "error";
        }
    }
}
