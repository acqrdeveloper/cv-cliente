<?php

// Recado
Route::get('/recado', function(){ return view('app'); });
Route::get('/recado/search/{anio}/{mes}/{estado}', 	'CV\recadoController@search');
Route::get('/recado/{id}/history', 					'CV\recadoController@getHistory');