<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class RecadoHistorial extends Model { 
	//protected $connection = 'centros';
	protected $table = 'recado_historial';
	protected $fillable = [
		'id',		'recado_id',	'usuario',
		'observacion',	'fecha',	'contenido',
		'para',		'entregado_a',	'lugar'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}