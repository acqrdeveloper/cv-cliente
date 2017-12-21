<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Notification extends Model { 
	//protected $connection = 'centros';
	protected $table = 'notification';
	protected $fillable = [
		'id',	'creado_por',	'content',	'modulo',
		'descripcion',	'empresa_nombre',	'url',
		'created_at',	'tipo'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}