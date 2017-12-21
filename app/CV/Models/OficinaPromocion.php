<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class OficinaPromocion extends Model { 
	//protected $connection = 'centros';
	protected $table = 'oficina_promocion';
	protected $fillable = [
		'local_id', 'modelo_id', 'plan_id', 
		'desde', 'hasta', 'precio', 
		'tipo', 'created_at', 'updated_at'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}
