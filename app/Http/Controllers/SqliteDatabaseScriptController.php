<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
Use SQLite3;
use  File;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;

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
            } catch(Exception $exception) { 
                echo $exception->getMessage();
            }
        } else {
            echo "table is not exist for this database";
        }
    }

    public function test() {
        
    }

    public function exportStructureCreateDatabase() {
        if(File::exists(public_path('allSiteDatabase/commonDatabaseStructure.sql'))) {
            File::delete(public_path('allSiteDatabase/commonDatabaseStructure.sql'));
        }
        exec('mysqldump --user=root --password="HdwfQrD!rtsC4Ij&" --no-data nextjs > /var/www/html/api/public/allSiteDatabase/commonDatabaseStructure.sql');
        if(File::exists(public_path('allSiteDatabase/commonDatabaseStructure.sql'))) {
            DB::statement("CREATE DATABASE demo");
            config(['database.connections.onthefly' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => 'demo',
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
            ]]);
            DB::connection('onthefly');
            DB::connection('onthefly')->unprepared(file_get_contents(public_path('allSiteDatabase/commonDatabaseStructure.sql')));
        }
    }
}
