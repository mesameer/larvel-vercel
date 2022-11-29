<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Blog;
use Helpers;
class BlogController extends Controller
{
    public function index(Request $request) { 
        $data = [];
        $fullArray = [];
        $blogData = Blog::on('onthefly')
                    ->select('*')
                    ->get()->toArray();
        if(!empty($blogData)) {
            foreach($blogData as $result) {
                $data['title'] = $result['title'];
                $data['description'] = $result['description'];
                $data['image'] = $result['image'];
                $data['created_at'] = $result['created_at'];
                $data['href'] =  Helpers::makeUrl($result['title']);
                $fullArray[] = $data;
            }
            return response()->json($fullArray);
        } else {
            $myArray = ['response'=>'blog is not exist for this domain'];
            return response()->json($myArray,400); 
        }
        
    }
}
