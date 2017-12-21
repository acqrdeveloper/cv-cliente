<?php

// Correspondencia
Route::get('/correspondencia', function(){ return view('app'); });
Route::get('/cdr/report/{anio}/{mes}', 'CV\cdrController@report');
