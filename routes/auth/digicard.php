<?php

Route::get('/digicard/info', 'Digicard\masterController@getInfo');
Route::post('/digicard/info', 'Digicard\masterController@setInfo');
Route::post('/digicard/producto', 'Digicard\masterController@createProduct');