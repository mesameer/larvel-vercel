<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
Use SQLite3;
use  File;

class SqliteDatabaseScriptController extends Controller
{
    public function index(Request $request) {
        $tableData = '';
        $allTables = DB::Connection('onthefly')->select('SHOW TABLES');
        $databaseName = DB::Connection('onthefly')->getDatabaseName();
        $tables = '';
        $tableInDatabase ='Tables_in_'.$databaseName; 
        if(!empty($allTables)) {
              foreach($allTables as $key => $row) {
                $allRecord =  DB::Connection('onthefly')->getSchemaBuilder()->getColumnListing($row->$tableInDatabase);
                $tables.="CREATE TABLE " .$row->$tableInDatabase. "(";
                foreach($allRecord as $result) {
                    if($result == 'id') {
                        $tables.="'id'	INTEGER NOT NULL,";
                    } else {
                        $tables.="'$result'	TEXT,";
                    }
                }  
                $tables.=' PRIMARY KEY("id" AUTOINCREMENT)';
                $tables.=');';
            }
            try {
                if(File::exists(public_path('allSiteDatabase/'.$databaseName.'.sqlite'))) {
                    File::delete(public_path('allSiteDatabase/'.$databaseName.'.sqlite'));
                }
                $db = new SQLite3('allSiteDatabase/'.$databaseName.'.sqlite');
                $db->exec($tables);
                foreach($allTables as $key => $table) {
                    $newValue = '';
                    $tableData = '';
                    $tableData = DB::Connection('onthefly')->table($table->$tableInDatabase)->get()->toArray();
                    foreach($tableData as $key => $row) {
                        $newValue = array_map(function($val) { return str_replace("'","''",$val); }, (array)$row);
                        $response = $db->exec("INSERT INTO " .$table->$tableInDatabase."(". "'" . implode ( "', '", array_keys($newValue) ) . "'" .") VALUES (". "'" . implode ( "', '", array_values($newValue) ) . "'" .")"); 
                    }
                }
                if(File::exists(public_path('allSiteDatabase/'.$databaseName.'.sqlite'))) {
                    echo "$databaseName database is generated succesfully";
                }
            } catch(Exception $exception) { 
                echo $exception->getMessage();
            }
        } else {
            echo "table is not exist for this database";
        }
    }
}
