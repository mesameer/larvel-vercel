<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\DatabaseConfiguration;
use DB;
use Config;
class DatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    */

    public function handle(Request $request, Closure $next) {
        if(!empty($request->domain)) {
            $databaseInformation = DB::table("site_database_name")
            ->select("database_name")
            ->where("domain", $request->domain)
            ->first();
            if(!empty($databaseInformation->database_name)) {                
                config(['database.connections.onthefly' => [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST', '127.0.0.1'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => $databaseInformation->database_name,
                    'username' => env('DB_USERNAME', 'root'),
                    'password' => env('DB_PASSWORD', ''),
                ]]);
                DB::connection('onthefly');
           } else {
                $myArray = ['response'=>'This domain database information is not exist'];
                return response()->json($myArray);
           }
        } else {
            $myArray = ['response'=>'please pass the domain infomation in your request'];
            return response()->json($myArray);
        }
        return $next($request);
    }
}
