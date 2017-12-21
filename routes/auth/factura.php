<?php
// Factura
Route::get('/factura', function(){ return view('app'); });
Route::get('/factura/search', 'CV\facturaController@search');


Route::get('/nota/search', 'CV\facturaController@nota_search');

Route::get('/factura/getone/{factura_id}', 'CV\facturaController@getone');
Route::get('/factura/report_pagos/{anio}/{mes}', 'CV\facturaController@report_pagos');
Route::get('/factura/factura_item/{factura_id}', 'CV\facturaController@factura_item');
Route::get('/factura/payment_detail/{factura_id}', 'CV\facturaController@payment_detail');
Route::get('/factura/factura_historial/{factura_id}', 'CV\facturaController@factura_historial');
Route::get('/factura/report_facturacion/{anio}/{mes}', 'CV\facturaController@report_facturacion');
Route::get('/factura/facturacion_empresas/{anio}/{mes}/{ciclo}', 'CV\facturaController@facturacion_empresas');

