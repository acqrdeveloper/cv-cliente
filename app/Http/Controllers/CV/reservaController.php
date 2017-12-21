<?php namespace CVClient\Http\Controllers\CV;
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Origin: *');
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\ReservaRepo;
use CVClient\CV\Repos\AsistenciaRepo;
use CVClient\Http\Controllers\AppAuth;

class reservaController extends Controller {

    public function uploadList( $reservaID ){
        try{
            $v = $request->all();
            return ( new AsistenciaRepo )->massive( $reservaID, $v["estructura"] );
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }


    public function auditorioprecio( $localID, $modeloID, $planID ){
        try{
            $numout = 0;
            return response()->json(( new ReservaRepo )->auditorioprecio( $localID, $modeloID, $planID ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function disponiblehorario( $fechaR, $oficinaID, $reservaID, $vhini, $vhfin ){
        try{
            $numout = 0;
            return ( new ReservaRepo )->disponiblehorario( $fechaR, $oficinaID, $reservaID, $vhini, $vhfin );
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function cocheradisponibility( $reservaID, $fReserva, $localID, $vhini, $vhfin ){
        try{
            return ( new ReservaRepo )->cocheradisponibility( $reservaID, $fReserva, $localID, $vhini, $vhfin );
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function getInviteList($reservaID){
        try{
            return response()->json(( new ReservaRepo )->getInviteList( $reservaID ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function getInviteTotal($reservaID){
        try{
            return response()->json(( new ReservaRepo )->getInviteTotal( $reservaID ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function disponibility(){
        try{
            $fecha = request()->input('fecha');
            $oficinaID = request()->input('oficina_id');
            return response()->json(( new ReservaRepo )->disponibility( $fecha, $oficinaID ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function create( Request $request ){
        try{

            $user = \Auth::user();

            if( $user->preferencia_estado != 'A' && $user->preferencia_estado != 'X'  )
                throw new \Exception("Su cuenta se encuentra suspendida, regularice su situación llamando al (01) 707-3500 Anexo 305.");
            
            $params = $request->all();
            $params["empresa_id"] = $user->id;
            $data = ( new ReservaRepo )->create( $params );
            if(!($data['id']>0)){
                throw new \Exception( $data['mensaje'] );
            }else{
                $data["reserva"] = ( new ReservaRepo )->reservaById( $data['id'] );
            }
            return response()->json($data, 200);

        } catch(\Exception $ex) {
            return response()->json(["message" => $ex->getMessage(), "extra" => $ex->getLine()." ".$ex->getFile() ], 412);
        }
    }

    public function search($fecha = null){
        try{
            $getparams = request()->all();
            return response()->json(( new ReservaRepo )->search($fecha, $getparams));
        } catch(\Exception $ex) {
            return response()->json(["message" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function cancel(){
        try{
            return response()->json( ( new ReservaRepo )->cancel( request()->all() )[0] , 200);
        } catch(\Exception $ex) {
            return response()->json(["message" => $ex->getMessage()], 412);
        }
    }
}