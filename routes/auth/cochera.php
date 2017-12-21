<?php

// Reserva
Route::get('/cochera', function(){ return view('app'); });
Route::get('/cochera/{reservaID}/{fReserva}/{localID}/{vhini}/{vhfin}', 'CV\cocheraController@disponibility');
