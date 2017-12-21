<?php

// Correspondencia y recados
Route::get('/recado-correspondencia/{tab?}', function(){ return view('app'); });

// Rutas de correspondencia
Route::get('/correspondencia/search/{anio}/{mes}/{estado}', 'CV\correspondenciaController@search');
Route::get('/correspondencia/report/{anio}/{mes}', 'CV\correspondenciaController@report');

Route::get('/correspondencia/{id}/history', 'CV\correspondenciaController@getHistory');


// Rutas de recado