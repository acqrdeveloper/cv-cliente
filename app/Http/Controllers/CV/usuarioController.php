<?php
/**
 * Created by PhpStorm.
 * User: aquispe
 * Date: 2017/05/25
 * Time: 11:47
 */

namespace CVClient\Http\Controllers\CV;

use CVClient\CV\Repos\UsuarioRepo;
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;

class usuarioController extends Controller
{
    public function __construct(Request $request, UsuarioRepo $usuarioRepo)
    {
        $this->request = $request;
        $this->repo = $usuarioRepo;
    }

    function store()
    {
        $rpta = $this->repo->_store($this->request);
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function update($id)
    {
        $rpta = $this->repo->_update($id, $this->request);
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function getListPermisos()
    {
        $rpta = $this->repo->_getListPermisos();
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function getListModulos()
    {
        $rpta = $this->repo->_getListModulos();
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function listAll()
    {
        $rpta = $this->repo->_listAll($this->request->all());
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function getListRoles()
    {
        $rpta = $this->repo->_getListRoles();
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function updateRoles($id)
    {
        $rpta = $this->repo->_updateRoles($id, $this->request);
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function changeEstado($id)
    {
        $rpta = $this->repo->_changeEstado($id, $this->request);
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }


}