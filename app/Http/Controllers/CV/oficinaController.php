<?php namespace CVClient\Http\Controllers\CV;
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Origin: *');
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\OficinaRepo;
class oficinaController extends Controller {

    public function getSpaceByTime(){
        $code = 200;
        try {
            $params = request()->all();
            /**
             * local_id
             * modelo_id
             * fecha
             * hini
             * hfin
             **/
            $response = \DB::select('CALL AL_OFICINA_DISPONIBILIDAD_LISTA(?,?,?,?,?,?)', [$params['local_id'], $params['modelo_id'], $params['fecha'], $params['hini'], $params['hfin'], $params['reserva_id']]);
        } catch (\Exception $e) {
            $response = ['message'=>$e->getMessage()];
            $code = 500;
        }

        return response()->json($response, $code);
    }  

    public function disponibility(){
        try{
        	$fecha = request()->input('fecha');
        	$oficinaID = request()->input('oficina_id');
            return response()->json(( new OficinaRepo )->disponibility( $fecha, $oficinaID ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function getOficinaList(Request $request){
        try{
            $getparams = $request->all();
            return response()->json(( new OficinaRepo )->getOficinaList( $getparams ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }
    
}