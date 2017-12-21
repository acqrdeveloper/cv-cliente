<?php
Route::get('/reserva', 			function(){ return view('app'); });
Route::get('/reserva/create', 	function(){ return view('app'); });

Route::get('/reserva/search/{fecha?}', 											'CV\reservaController@search');
Route::get('/reserva/oficina/disponibilidad', 									'CV\reservaController@disponibility');
Route::get('/reserva/cochera/{reservaID}/{fReserva}/{localID}/{vhini}/{vhfin}', 'CV\reservaController@cocheradisponibility');

Route::get('/reserva/disponible/{fechaR}/{oficinaID}/{reservaID}/{vhini}/{vhfin}', 'CV\reservaController@disponiblehorario');

Route::get('/reserva/auditorio/{localID}/{modeloID}/{planID}', 'CV\reservaController@auditorioprecio');


Route::get('/reserva/invitados/{reservaID}', 									'CV\reservaController@getInviteList');
Route::get('/reserva/invitadostotal/{reservaID}', 								'CV\reservaController@getInviteTotal');

Route::post('/reserva', 														'CV\reservaController@create');
Route::post('/reserva/{reservaID}', 											'CV\reservaController@uploadList');
 
Route::delete('/reserva',														'CV\reservaController@cancel');