<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Factura extends Model { 
	protected $table = 'factura';
	protected $fillable = [
		'id', 	'empresa_id', 	'numero', 	'fecha_creacion', 	'monto', 
		'monto_fisico', 'estado', 'fecha_emision', 'fecha_vencimiento', 'fecha_limite', 
		'fecha_pago', 'comprobante', 'primera_factura', 'accion', 'moneda', 
		'usuario', 'observacion_pago', 'usuario_pago', 'garantia_uso', 'auditorio', 
		'entregado_a', 'su_state', 'su_info', 'mail_send', 'fecha_entrega_boleta', 'detraccion'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}


