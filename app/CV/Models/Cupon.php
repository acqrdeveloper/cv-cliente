<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Cupon extends Model { 
	protected $table = 'cupon';
	protected $fillable = [ 'id', 'codigo', 'created_at', 'updated_at', 'reserva_id', 'finicio', 'ffin', 'usado', 'monto', 'usuario' ];
	public    $timestamps = false;
	protected $hidden     = [];
}