<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class EmpresaHistorial extends Model { 
	protected $table = 'empresa_historial';
	protected $fillable = ['empresa_id','estado','observacion','empleado','fecha'];
	public $timestamps = false;
}