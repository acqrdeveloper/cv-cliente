<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Modelo extends Model { 
	//protected $connection = 'centros';
	protected $table = 'modelo';
	protected $fillable = ['id', 'nombre'];
	public    $timestamps = false;
	protected $hidden     = [];
}