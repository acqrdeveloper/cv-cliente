<?php

Route::get('/oficina', function(){ return view('app'); });

/**
 * Obtiene los horarios disponibles de una oficina en una fecha determinada
 * Request parameters
 * @param string fecha Fecha de la reserva
 * @param int oficina_id Id de la oficina
 */
Route::get('/oficina/disponibilidad', 'CV\oficinaController@disponibility');
Route::get('/oficina/disponibilidad.v1', 'CV\oficinaController@getSpaceByTime');