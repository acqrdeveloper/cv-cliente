<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class CorrespondenciaHistorial extends Model { 
	//protected $connection = 'centros';
	protected $table = 'correspondencia_historial';
	protected $fillable = [
		'id', 'correspondencia_id',	'observacion',
		'usuario', 'fecha', 'remitente',
		'asunto', 'entregado_a', 'lugar'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}