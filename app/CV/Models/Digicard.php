<?php namespace CVClient\CV\Models;

use Illuminate\Database\Eloquent\Model;

class Digicard extends Model { 

	protected $table = 'empresa_digicard';

	protected $primaryKey = 'empresa_id';

	protected $fillable = ['empresa_id','nombre','empresa_nombre','empresa_ruc','descripcion','telefono','anexo','pais','whatsapp','web','profesion','local_id','email'];

	protected $hidden = ['created_at','updated_at'];

}