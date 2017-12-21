<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Plan extends Model { 
	protected $table = 'plan';
	protected $fillable = ['cantidad_copias', 'cantidad_impresiones', 'cochera', 'descripcion', 'horas_privada', 'horas_reunion', 'nombre', 'precio', 'proyector', 'estado'];
	public $timestamps = false;

	public function getByState($estado){
		return self::where('estado', $estado)->get();
	}
}