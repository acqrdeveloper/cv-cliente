<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Cochera extends Model { 
	//protected $connection = 'centros';
	protected $table = 'cochera';
	protected $fillable = ['id', 'nombre', 'estado', 'lugar'];
	public    $timestamps = false;
	protected $hidden     = [];
}