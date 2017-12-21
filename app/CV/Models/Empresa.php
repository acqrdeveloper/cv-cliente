<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model {

	protected $table = 'empresa';
	protected $fillable = ['plan_id', 'central_id', 'central', 'preferencia_estado', 'preferencia_login', 'preferencia_contrasenia', 'preferencia_facturacion', 'preferencia_cdr', 'preferencia_fiscal', 'preferencia_fiscal_nro_partida', 'preferencia_comprobante', 'empresa_nombre', 'empresa_ruc', 'empresa_direccion', 'empresa_rubro', 'nombre_comercial', 'url_web', 'fac_nombre', 'fac_apellido', 'fac_dni', 'fac_email', 'fac_telefono', 'fac_celular', 'fac_domicilio', 'moneda', 'asesor', 'fecha_creacion', 'promo', 'flag_encuesta', 'convenio', 'convenio_duration', 'password','updated_at', 'carrera' ,'facebook_id','google_id','api_token'];

	public $timestamps = false;

	public function contrato(){
		return $this->hasOne('CVClient\CV\Models\Contrato', 'empresa_id', 'id');
	}

	public function pbx(){
		return $this->belongsTo('CVClient\CV\Models\Central','central_id','id');
	}

	public function empleados(){
		return $this->hasMany('CVClient\CV\Models\Empleado', 'empresa_id', 'id');
	}

	public function historial(){
		return $this->hasMany('CVClient\CV\Models\EmpresaHistorial', 'empresa_id', 'id');
	}

	public function representantes(){
		return $this->hasMany('CVClient\CV\Models\Representante', 'empresa_id', 'id');
	}
}

