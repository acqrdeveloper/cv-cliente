<?php
/**
 * Created by PhpStorm.
 * User: aquispe
 * Date: 2017/05/18
 * Time: 15:54
 */

Route::get('contratopdf/{id}/{attach}', 'CV\pdfController@contratoPDF')->name('contrato');
Route::get('html', 'CV\pdfController@contratoHTML');
Route::get('convert/{num}', 'CV\pdfController@fnConvertLetter');
Route::get('dataempresa/{id}', 'CV\pdfController@getDataEmpresa');