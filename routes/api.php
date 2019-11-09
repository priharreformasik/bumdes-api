<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
/*======== START OF AUTH ========*/
Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');
Route::get('details', 'API\UserController@details');
Route::group(['middleware' => 'auth:api'], function(){
Route::get('details', 'API\UserController@details');
});
Route::get('logout', 'API\UserController@logout')->middleware('auth:api');
Route::put('update/{id}', 'API\UserController@update')->middleware('auth:api');
Route::put('ganti_password/{id}', 'API\UserController@update_password_api')->middleware('auth:api');
Route::get('detail/{id}', 'API\UserController@show')->middleware('auth:api');

/*======== END OF AUTH ==========*/

/*======== START OF PARENT AKUN ==========*/
Route::middleware('auth:api')->get('parent-akun', 'ParentAkunController@parent');
Route::middleware('auth:api')->post('/parent-akun/store', 'ParentAkunController@store');
Route::middleware('auth:api')->put('/parent-akun/update/{id}', 'ParentAkunController@update');
Route::middleware('auth:api')->get('/parent-akun/detail/{id}', 'ParentAkunController@detail');
Route::middleware('auth:api')->get('/parent-akun/delete/{id}', 'ParentAkunController@destroy');
/*======== END OF PARENT AKUN ==========*/

/*======== START OF KLASIFIKASI AKUN ==========*/
Route::middleware('auth:api')->get('klasifikasi-akun', 'KlasifikasiAkunController@show');
Route::middleware('auth:api')->post('/klasifikasi-akun/store', 'KlasifikasiAkunController@store');
Route::middleware('auth:api')->put('/klasifikasi-akun/update/{id}', 'KlasifikasiAkunController@update');
Route::middleware('auth:api')->get('/klasifikasi-akun/detail/{id}', 'KlasifikasiAkunController@detail');
Route::middleware('auth:api')->get('/klasifikasi-akun/delete/{id}', 'KlasifikasiAkunController@destroy');
/*======== END OF KLASIFIKASI AKUN ==========*/

/*======== START OF DATA AKUN ==========*/
Route::middleware('auth:api')->get('data-akun', 'DataAkunController@show');
Route::middleware('auth:api')->post('/data-akun/store', 'DataAkunController@store');
Route::middleware('auth:api')->put('/data-akun/update/{id}', 'DataAkunController@update');
Route::middleware('auth:api')->get('/data-akun/detail/{id}', 'DataAkunController@detail');
Route::middleware('auth:api')->get('/data-akun/delete/{id}', 'DataAkunController@destroy');
/*======== END OF DATA AKUN ==========*/

/*======== START OF NERACA AWAL ==========*/
Route::middleware('auth:api')->get('neraca-awal', 'NeracaAwalController@show');
Route::middleware('auth:api')->get('neraca-awal/data-akun', 'NeracaAwalController@data_akun');
Route::middleware('auth:api')->get('neraca-awal/show_klasifikasi', 'NeracaAwalController@show_parent');
Route::middleware('auth:api')->get('neraca-awal/show_akun', 'NeracaAwalController@show_klasifikasi');
Route::middleware('auth:api')->get('neraca-awal/parent', 'NeracaAwalController@parent');
Route::middleware('auth:api')->post('/neraca-awal/store', 'NeracaAwalController@store');
Route::middleware('auth:api')->put('/neraca-awal/update/{id}', 'NeracaAwalController@update');
Route::middleware('auth:api')->get('/neraca-awal/detail/{id}', 'NeracaAwalController@detail');
Route::middleware('auth:api')->get('/neraca-awal/delete/{id}', 'NeracaAwalController@destroy');
/*======== END OF NERACA AWAL ==========*/

/*======== START OF JURNAL ==========*/
Route::middleware('auth:api')->get('jurnal', 'JurnalController@show');
Route::middleware('auth:api')->post('/jurnal/store', 'JurnalController@store');
Route::middleware('auth:api')->post('/jurnal/store_jurnal', 'JurnalController@store_jurnal');
Route::middleware('auth:api')->put('/jurnal/update/{id}', 'JurnalController@update');
Route::middleware('auth:api')->get('/jurnal/detail/{id}', 'JurnalController@detail');
Route::middleware('auth:api')->get('/jurnal/delete/{id}', 'JurnalController@destroy');
/*======== END OF JURNALL ==========*/


// Route::middleware('auth:api')->get('buku-besar', 'LaporanController@buku_besar');
//Route::middleware('auth:api')->get('laba-rugi', 'LaporanController@laba_rugi');
//Route::middleware('auth:api')->get('perubahan-modal', 'LaporanController@perubahan_modal');
//Route::middleware('auth:api')->get('neraca', 'LaporanController@neraca');


/*======== START OF LAPORAN ==========*/
Route::middleware('auth:api')->get('buku-besar', 'BukuBesarController@buku_besar');
Route::middleware('auth:api')->get('laba-rugi', 'LabaRugiController@laba_rugi');
Route::middleware('auth:api')->get('perubahan-modal', 'PerubahanEkuitasController@perubahan_modal');
Route::middleware('auth:api')->get('neraca', 'NeracaController@neraca');
/*======== END OF LAPORAN ==========*/
