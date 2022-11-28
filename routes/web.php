<?php

use App\Http\Controllers\DomainResearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SqliteDatabaseScriptController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => true,]);

Route::group(['middleware' => ['auth']], function () {
    Route::get('/domain_report', [DomainResearchController::class, 'domainReport'])->name('domainReport');
    Route::get('/search_history', [DomainResearchController::class, 'searchHistory'])->name('searchHistory');

    Route::get('/manage_industries', [DomainResearchController::class, 'manageIndustries'])->name('manageIndustries');
    Route::post('/add_industry', [DomainResearchController::class, 'addIndustry'])->name('addIndustry');
    Route::get('/delete_industry/{id}', [DomainResearchController::class, 'deleteIndustry'])->name('deleteIndustry');

    Route::post('/toggle_favorite', [DomainResearchController::class, 'searchHistoryToggleFavorite'])->name('searchHistoryToggleFavorite');
    Route::post('/delete_search_history', [DomainResearchController::class, 'deleteSearchHistory'])->name('deleteSearchHistory');

    Route::post('/available_domains', [DomainResearchController::class, 'getDomainByLocation'])->name('getDomainByLocation');

    Route::get('/autocomplete_city', [DomainResearchController::class, 'allUniqueCities'])->name('autocomplete_city');
    Route::get('/generate_domains', [DomainResearchController::class, 'allUniqueCities'])->name('autocomplete_city');

    Route::post('/get_zip_codes', [DomainResearchController::class, 'getZipCodes'])->name('getZipCodes');
    Route::post('/get_location_group_zip_codes', [DomainResearchController::class, 'getLocationGroupZipCodes'])->name('getLocationGroupZipCodes');

    Route::post('/save_zip_codes', [DomainResearchController::class, 'addZipCodes'])->name('addZipCodes');
    Route::post('/save_search_history', [DomainResearchController::class, 'saveSearchHistory'])->name('saveSearchHistory');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware('database')->group(function () {
    Route::get('/convert-mysql-sqlite', [SqliteDatabaseScriptController::class, 'convertMysqlSqlite']);
});
Route::get('/export-structure-create-database', [SqliteDatabaseScriptController::class, 'exportStructureCreateDatabase']);
