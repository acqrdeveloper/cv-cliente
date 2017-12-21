<?php
/**
 * Created by PhpStorm.
 * User: QuispeRoque
 * Date: 26/04/17
 * Time: 17:10
 */

namespace CVClient\CV\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed mensaje
 */
class Mensaje extends Model
{

    protected $table='mensaje';
    protected $fillable = [
        'vfrom',
        'vto',
        'asunto',
        'mensaje',
        'mensaje',
        'usuario',
        'estado',
        'respondido',
        'leido',
        'respondido_user',
        'leido_user',
        'movil'
    ];
}