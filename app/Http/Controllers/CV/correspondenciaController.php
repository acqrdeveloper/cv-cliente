<?php namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\CorrespondenciaRepo;
/*
use CVClient\CV\Repos\UsuarioRepo;
*/
class correspondenciaController extends Controller {
    public function search( $anio, $mes, $estado, Request $request ){
        try{
            $getparams = $request->all();
            return response()->json(( new CorrespondenciaRepo )->search( $anio, $mes, $estado, $getparams ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }
    public function report( $anio, $mes, Request $request ){
        try{
            $getparams = $request->all();
            return response()->json(( new CorrespondenciaRepo )->report( $anio, $mes, $getparams ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }
    public function getHistory( $id ){
        try{
            return ( new CorrespondenciaRepo )->history( $id );
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getFile()." ".$ex->getLine() ], 412);
        }
    }
}