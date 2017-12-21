<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class FacturaItem extends Model { 
	protected $table = 'factura_item';
	protected $fillable = [
		'id', 'factura_id', 'descripcion', 
		'descripcion_sunat', 'precio', 'estado', 
		'factura_item_temporal_id', 'warranty', 'is_nota', 'tipo', 'anio', 'mes', 'custom_id'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}