<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class FacturaHistorial extends Model { 
	protected $table = 'factura_historial';
	protected $fillable = [
		'id', 		'empresa_id',	'factura_id',
		'item_id',	'tipo',			'observacion',
		'usuario',	'fecha'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}