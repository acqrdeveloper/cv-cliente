<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Configuracion extends Model { 
	protected $table = 'configuracion';
	protected $fillable = ['id', 'nombre', 'valor', 'otro', 'descripcion', 'edit'];
	public    $timestamps = false;
	protected $hidden     = [];
}