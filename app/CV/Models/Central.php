<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Central extends Model { 

	protected $table = 'central';

	public $timestamps = false;

	public function opciones(){
		return $this->hasMany('CVClient\CV\Models\CentralOpcion', 'central_id', 'id');
	}

	public function empresa(){
		return $this->hasOne('CVClient\CV\Models\Empresa','central_id','id');
	}
}