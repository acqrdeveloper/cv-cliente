<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class CentralOpcion extends Model { 

	protected $table = 'central_opcion';
	protected $fillable = ['id','opcion_numero', 'opcion_nombre', 'anexo_numero', 'anexo_nombre', 'redireccion'];
	public $timestamps = false;
}