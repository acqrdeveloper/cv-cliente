<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class ReservaInvitado extends Model { 
	protected $table = 'reserva_invitado';
	protected $fillable = [
		'reserva_id', 'dni', 'nomape', 'email', 'movil', 
		'created_at', 'updated_at', 'estado', 'asistencia', 'nuevo'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}