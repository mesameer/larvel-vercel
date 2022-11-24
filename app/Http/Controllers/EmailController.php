<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Mail\SendMail;
class EmailController extends Controller
{
    public function contactForm(Request $request) {
        if(empty($request->contactName)) {
            return  response()->json(['response' => 'contact name field is required'],400);
        }
        if(empty($request->email)) {
            return  response()->json(['response' => 'email field is required'],400);
        }
        if(empty($request->phone)) {
            return response()->json(['response' => 'phone field is required'],400);
        }
        $mailData = [
            'domain' => $request->domain,
            'contactName' => $request->contactName,
            'email' => $request->email,
            'phone' => $request->phone,
        ];
        if(Mail::to('alan@logicalcommerce.com')->send(new SendMail($mailData))) {
            return  response()->json(['response' => 'Email successfully sent']);
        } else {
            return  response()->json(['response' => 'Email failed'],400);
        }
    }
}
