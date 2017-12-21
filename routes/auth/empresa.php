<?php
Route::get('/profile', function(){ return view('app'); });
Route::put('/profile/setfactura', 	'CV\empresaController@changeFacturaInfo');
Route::put('/profile/setlogin', 	'CV\empresaController@changeLogin');
Route::put('/profile/setpass', 		'CV\empresaController@changePassword');