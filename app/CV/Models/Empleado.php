<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model { 

	protected $table = 'empresa_empleados';
	protected $fillable = ['id', 'empresa_id', 'nombre', 'apellido', 'dni', 'correo', 'fecha', 'estado', 'opcion_central_id'];

	public $timestamps = false;
}