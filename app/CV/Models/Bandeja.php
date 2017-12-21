<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Bandeja extends Model { 
	//protected $connection = 'centros';
	protected $table    = 'bandeja';
	protected $fillable = [
		'id',		'empresa_id',
		'de_tipo',	'de',		'a_tipo',	'a',
		'asunto',	'mensaje',	'leido',	'quienleyo',
		'padre_id',	'respuesta_id',
		'created_at',	'updated_at'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}