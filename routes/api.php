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

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: POST,GET,PUT,DELETE,HEAD,OPTIONS');

Route::get('/mobile/{funcion}',     'CV\mobileController@funcion');

Route::put('/company/{id}', 'CV\mobileController@setPushToken');

Route::group(['middleware'=>['auth:api']], function(){
	//Route::get('/mobile/{funcion}',     'CV\mobileController@funcion');
	foreach (glob(__DIR__."/auth/*.php") as $filename){
		require $filename;
	}
});