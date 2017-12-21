<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class BandejaMensaje extends Model { 
	//protected $connection = 'centros';
	protected $table    = 'bandeja_mensaje';
	protected $fillable = [ 'id', 'mensaje' ];
	public    $timestamps = false;
	protected $hidden     = [];
}