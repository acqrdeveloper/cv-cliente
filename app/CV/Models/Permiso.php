<?php
/**
 * Created by PhpStorm.
 * User: aquispe
 * Date: 2017/05/31
 * Time: 16:11
 */

namespace CVClient\CV\Models;


use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = "permisos";
    protected $fillable = [
        'modulo',
        'pages',
        'nombre',
        'grupo',
        'place',
        'estado',
    ];
}