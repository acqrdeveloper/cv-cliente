<?php namespace CVClient\CV\Models;
use Illuminate\Database\Eloquent\Model;
class Crm extends Model{
    public $timestamps = false;
    protected $table = 'crm';
    protected $fillable = [
        'empleado',
        'crm_tipo_id',
        'empresa_id',
        'nota',
        'fecha',
        'hora',
        'fecha_creacion',
        'estado',
        'archivado_por',
        'fecha_archivado',
        'visto',
        'usuario_id',
        ];
}