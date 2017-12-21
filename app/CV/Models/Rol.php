<?php
/**
 * Created by PhpStorm.
 * User: aquispe
 * Date: 2017/05/31
 * Time: 16:08
 */

namespace CVClient\CV\Models;


use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{

    protected $table = 'roles';
    protected $fillable = [
        'page',
        'action'
    ];

}