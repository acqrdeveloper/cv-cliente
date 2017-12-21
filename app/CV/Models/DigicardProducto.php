<?php namespace CVClient\CV\Models;

use Illuminate\Database\Eloquent\Model;

class DigicardProducto extends Model { 

	protected $table = 'empresa_digicard_producto';

	protected $fillable = ['empresa_id','nombre','descripcion','precio'];

	protected $hidden = ['created_at','updated_at'];

	public static function findByCompany($empresa_id){
		return self::where('empresa_id', $empresa_id)->orderBy('id','DESC')->get();
	}

}