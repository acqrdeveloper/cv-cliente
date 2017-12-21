<?php
/**
 * Created by PhpStorm.
 * User: QuispeRoque
 * Date: 18/04/17
 * Time: 13:18
 */

Route::get('feedback', function(){ return view('app'); });
Route::get('getFeedback','CV\feedbackController@getFeedback');
Route::get('getList','CV\feedbackController@getList');
