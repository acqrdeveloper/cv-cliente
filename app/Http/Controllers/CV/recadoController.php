<?php namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\RecadoRepo;
/*
use CVClient\CV\Repos\UsuarioRepo;
*/
class recadoController extends Controller {
    public function search( $anio, $mes, $estado, Request $request ){
        try{
            $getparams = $request->all();
            return response()->json(( new RecadoRepo )->search( $anio, $mes, $estado, $getparams ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }
    public function getHistory( $id ){
        try{
            return ( new RecadoRepo )->history( $id );
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getFile()." ".$ex->getLine() ], 412);
        }
    }
}