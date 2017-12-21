<?php
// Dashboard
Route::get('/dashboard', function(){ return view('app'); });
Route::get('/dashboard/initial', 		'CV\dashboardController@initial');
Route::get('/dashboard/empresa', 		'CV\dashboardController@empresa');
Route::get('/dashboard/empresaestado', 	'CV\dashboardController@empresaestado');
Route::get('/dashboard/horario', 		'CV\dashboardController@horario');