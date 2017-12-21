<?php 
Route::get('/pbx', 'CV\centralController@getPbx');

/*
Route::get('/empresa/{empresa_id}/central/search', 'CV\centralController@getPbx');
Route::put('/empresa/{empresa_id}/central/cdr', 'CV\centralController@assignCdr');

Route::put('/central/config', 'CV\centralController@updateConfig');
Route::post('/central/opcion', 'CV\centralController@addOption');
Route::put('/central/opcion', 'CV\centralController@updateOption');
Route::delete('/central/{central_id}/opcion/{id}', 'CV\centralController@deleteOption');*/