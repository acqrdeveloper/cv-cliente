<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Oficina extends Model { 
	//protected $connection = 'centros';
	protected $table = 'oficina';
	protected $fillable = ['id', 'local_id', 'modelo_id', 'tipo', 'nombre_o', 'estado', 'piso_id', 'imagen', 'capacidad'];
	public    $timestamps = false;
	protected $hidden     = [];
}


