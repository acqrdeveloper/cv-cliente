<?php
Route::get('abogado', function () {    return view('app');});
Route::get('/abogado/search', 			'CV\abogadoController@search');
Route::post('/abogado', 				'CV\abogadoController@create');
Route::put('/abogado/{id}/{estado}', 	'CV\abogadoController@update');