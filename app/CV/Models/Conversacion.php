<?php
/**
 * Created by PhpStorm.
 * User: QuispeRoque
 * Date: 26/04/17
 * Time: 17:28
 */

namespace CVClient\CV\Models;


use Illuminate\Database\Eloquent\Model;


class Conversacion extends Model
{
    protected $table='conversacion';

    protected $fillable = [
        'mensaje_id',
        'respuesta',
        'empleado',
        'leido',
        'leido_user',
        'movil',
    ];
}