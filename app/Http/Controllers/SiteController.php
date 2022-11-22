<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\SiteApi;
class SiteController extends Controller
{
    public function index(Request $request) { 
        $api = new SiteApi();
        return $siteApiResponse = $api->generateApiResponse($request->all());
    }
}
