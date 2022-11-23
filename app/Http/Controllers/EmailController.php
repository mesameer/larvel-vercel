<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Mail\SendMail;
class EmailController extends Controller
{
    public function contactForm(Request $request) {
        $mailData = [
            'domain' => $request->domain,
            'contactName' => $request->contactName,
            'email' => $request->email,
            'phone' => $request->phone,
        ];
        if(Mail::to('shahjad.ahmad89@gmail.com')->send(new SendMail($mailData))) {
            return json_encode(['status' => '200','message' => 'Email successfully sent']);
        } else {
            return json_encode(['status' => '500','message' => 'something went wrong']);
        }
    }
}
