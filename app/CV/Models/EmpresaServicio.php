<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class EmpresaServicio extends Model { 
	protected $table = 'empresa_servicio';
	protected $fillable = [
		'id', 'servicio_extra_id', 'empresa_id',
		'mes', 'tipo', 'monto', 'concepto'
	];
	public $timestamps = false;
}