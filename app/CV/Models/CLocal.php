<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class CLocal extends Model { 
	//protected $connection = 'centros';
	protected $table = 'clocal';
	protected $fillable = [
		'id',	'nombre',	'direccion',
		'estado',	'distrito',	'tipo',	
		'lo_hora_inicio',	'lo_hora_fin'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}

