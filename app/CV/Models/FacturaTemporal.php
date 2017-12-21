<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class FacturaTemporal extends Model { 
	protected $table = 'facturacion_temporal';
	protected $fillable = [
		'id', 'empresa_id', 'fecha_creacion', 'descripcion',
		'estado', 'precio', 'periodo', 'ex', 'reserva_id', 'cochera_proyector'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}