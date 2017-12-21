<?php
namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\CrmRepo;
class crmController extends Controller
{
    function getListUsers()
    {
        $rpta = ( new CrmRepo )->getListUsers();
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function getListPlanes()
    {
        $rpta = ( new CrmRepo )->getListPlanes();
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function getListCrm()
    {

        $rpta = ( new CrmRepo )->getListCrm();
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
    }

    function getList()
    {
        $getparams = request()->all();        
        $rpta = ( new CrmRepo )->getList($getparams);
        if ($rpta['load']) {
            return response()->json($rpta, 200);
        } else {
            return response()->json($rpta, 412);
        }
        /**/
    }

    function getNotesByEnterprice()
    {
        $getparams = request()->all();     
        $rpta = ( new CrmRepo )->getNotesByEnterprice($getparams);
        if($rpta['load']){
            return response()->json($rpta,200);
        }else{
            return response()->json($rpta,412);
        }
    }

    function postCreateNote()
    {   
        $getparams = request()->all();     
        $rpta = ( new CrmRepo )->postCreateNote($getparams);
        if($rpta['load']){
            return response()->json($rpta,200);
        }else{
            return response()->json($rpta,412);
        }
        
    }
}