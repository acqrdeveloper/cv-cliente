<?php
Route::get('/servicio', function(){ return view('app'); });
Route::get('/servicio/{empresa_id}/{anio}/{mes}', 'CV\servicioController@getRecursoPeriodo');
