<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class FacturaNota extends Model { 
	protected $table = 'factura_notas';
	protected $fillable = [
		'id', 'factura_id', 'numero', 
		'observacion', 'precio', 'empleado', 
		'tipo', 'fecha_emision', 'fecha_creacion', 
		'factura_notascol', 'su_state', 'su_info', 
		'cod_discrepancia', 'mail_send'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}