<?php
/**
 * Created by PhpStorm.
 * User: QuispeRoque
 * Date: 18/04/17
 * Time: 13:38
 */

namespace CVClient\CV\Models;


use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'empresa_id',
        'sugerencia',
        'tipo',
        'fecha_creacion',
        'estado',
        'movil',
    ];
}