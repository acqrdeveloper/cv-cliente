<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Abogado extends Model { 
	//protected $connection = 'centros';
	protected $table    = 'caso';
	protected $fillable = [
		'id',			'created_at',		'caso',
		'demandado',	'demandante',		'estado',
		'empresa_id',	'updated_at'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}

