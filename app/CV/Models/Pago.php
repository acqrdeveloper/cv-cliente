<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Pago extends Model { 
	protected $table = 'pago';
	protected $fillable = [
		'id', 'tipo', 'fecha_creacion', 'deposito_banco', 
		'deposito_cuenta', 'deposito_fecha', 'detalle', 
		'observacion', 'monto', 'factura_id', 'usuario', 
		'pago_factura_id', 'id_pos', 'dif_dep_pos', 
		'des_com_pos', 'detraccionD', 'detraccionE'
	];
	public    $timestamps = false;
	protected $hidden     = [];
}
