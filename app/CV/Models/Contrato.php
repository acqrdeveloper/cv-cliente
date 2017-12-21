<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Contrato extends Model { 
	protected $table = 'contrato';
	protected $fillable = ['empresa_id','fecha_inicio','fecha_fin','estado'];
	public $timestamps = false;
}