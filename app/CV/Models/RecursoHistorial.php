<?php namespace CVClient\CV\Models;

use Illuminate\Database\Eloquent\Model;

class RecursoHistorial extends Model {

	protected $table = 'recursos_historial';

	public function scopeGetReserveHistory($query, $reserva_id){
		return $query->where('reserva_id', $reserva_id);
	}

}