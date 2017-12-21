<?php
/**
 * Created by PhpStorm.
 * User: QuispeRoque
 * Date: 21/04/17
 * Time: 11:22
 */

Route::get('mensaje', function () {
    return view('app');
});
Route::get('list-conversations', 'CV\mensajeController@getConversationsList');
Route::get('conversations', 'CV\mensajeController@conversations');
Route::post('conversation', 'CV\mensajeController@conversation');
