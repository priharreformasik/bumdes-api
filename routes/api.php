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

/*======== END OF AUTH ==========*/

/*======== START OF PARENT AKUN ==========*/
Route::middleware('auth:api')->get('parent-akun', 'ParentAkunController@parent');
Route::middleware('auth:api')->post('/parent-akun/store', 'ParentAkunController@store');
Route::middleware('auth:api')->put('/parent-akun/update/{id}', 'ParentAkunController@update');
Route::middleware('auth:api')->get('/parent-akun/delete/{id}', 'ParentAkunController@destroy');
/*======== END OF PARENT AKUN ==========*/

/*======== START OF PARENT AKUN ==========*/
Route::middleware('auth:api')->get('klasifikasi-akun', 'KlasifikasiAkunController@show');
Route::middleware('auth:api')->post('/klasifikasi-akun/store', 'KlasifikasiAkunController@store');
Route::middleware('auth:api')->put('/klasifikasi-akun/update/{id}', 'KlasifikasiAkunController@update');
Route::middleware('auth:api')->get('/klasifikasi-akun/delete/{id}', 'KlasifikasiAkunController@destroy');
/*======== END OF PARENT AKUN ==========*/

/*======== START OF DATA AKUN ==========*/
Route::middleware('auth:api')->get('data-akun', 'DataAkunController@show');
Route::middleware('auth:api')->post('/data-akun/store', 'DataAkunController@store');
Route::middleware('auth:api')->put('/data-akun/update/{id}', 'DataAkunController@update');
Route::middleware('auth:api')->get('/data-akun/delete/{id}', 'DataAkunController@destroy');
/*======== END OF DATA AKUN ==========*/

/*======== START OF NERACA AWAL ==========*/
Route::middleware('auth:api')->get('neraca-awal', 'NeracaAwalController@show');
/*======== END OF NERACA AWAL ==========*/