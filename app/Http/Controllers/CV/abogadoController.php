<?php namespace CVClient\Http\Controllers\CV;
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Origin: *');
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\AbogadoRepo;
class abogadoController extends Controller {
    public function search(){
        try{
            $getparams  = request()->all(); 
            return response()->json(( new AbogadoRepo )->search( $getparams ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }
    public function create(){
        try{
            $getparams  = request()->all(); 
            return response()->json(( new AbogadoRepo )->create( $getparams ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }        
    }
    public function update($id,$estado){
        try{
            return response()->json(( new AbogadoRepo )->update( $id, $estado ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }        
    }
}