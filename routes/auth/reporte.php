<?php
Route::get('reporte', function(){ return view('app'); });
Route::get('reporte/{tipo}',	'CV\reporteController@reporte');
Route::get('export/{tipo}',	'CV\reporteController@export');