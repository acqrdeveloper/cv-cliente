<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class RecursoPeriodo extends Model {
	protected $table = 'recurso_periodo';
	protected $fillable = [
		'empresa_id', 		'anio', 					'mes',
		'cantidad_copias',	'cantidad_impresiones',		
		'horas_reunion',	'horas_privada',			'horas_capacitacion'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}