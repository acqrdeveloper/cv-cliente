<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Correspondencia extends Model { 
	//protected $connection = 'centros';
	protected $table = 'correspondencia';
	protected $fillable = [
		'id',	'empresa_id',	'fecha_creacion',
		'fecha_entrega',	'remitente',	'asunto',
		'entregado_a',	'nota',	'estado',	'creado_por',
		'entregado_por',	'cc',	'lugar',	'local_id',
		'qrcode',	'confirmado',	'updated_at'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}