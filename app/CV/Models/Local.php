<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Local extends Model {
	protected $table = 'clocal';
	protected $fillable = ['id', 'nombre', 'direccion', 'distrito', 'estado'];
	protected $hidden = ['lo_hora_fin','lo_hora_inicio','tipo'];
}