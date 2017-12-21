<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Reserva extends Model { 
	//protected $connection = 'centros';
	protected $table = 'reserva';
	protected $fillable = [
		'id',	'empresa_id',	'local_id',
		'oficina_id',	'fecha_reserva',
		'hora_inicio',	'hora_fin',
		'proyector',	'cochera_id',
		'placa',		'created_at',
		'estado',	'movil',	'observacion',
		'updated_at',	'creado_por'
	];
	public    $timestamps = false;
	protected $hidden     = [];

	public function scopeGetActive($query, $id = null){
		if(is_null($id)){
			return $query->where('estado','A');
		} else {
			return $query->where('id', $id)->where('estado','A');
		}
	}
}