<?php
namespace CVClient\CV\Repos;
use Auth;
use Mail;
use Carbon\Carbon;
use CVClient\CV\Models\RecursoHistorial;
use CVClient\CV\Models\Reserva;
use CVClient\CV\Models\ReservaDetalle;
use CVClient\CV\Models\Oficina;
use CVClient\CV\Models\OficinaPromocion;

use CVClient\CV\Repos\AsistenciaRepo;
use CVClient\Common\Repos\SessionRepo;
use CVClient\Common\Repos\QueryRepo;
class ReservaRepo{

    use \CVClient\Traits\NotificationTrait;

    public function disponiblehorario( $fechaR, $oficinaID, $reservaID, $vhini, $vhfin ){
        $disponibility = ( new SessionRepo )->CallRaw("mysql", "AL_RESERVA_DISP", [ $fechaR, $oficinaID, $reservaID, $vhini, $vhfin ] ) ;
        return $disponibility[0]["reservado"];        
    }

    public function cocheradisponibility( $reservaID, $fReserva, $localID, $vhini, $vhfin ){
        $disponibility = ( new SessionRepo )->CallRaw("mysql", "AL_COCHERA_DISPONIBILIDAD", [ $reservaID, $fReserva, $localID, $vhini, $vhfin ] ) ;
        return $disponibility;
    }
    
    public function disponibility( $fecha, $oficinaID ){
        $disponibility = ( new SessionRepo )->CallRaw("mysql", "AL_OFICINA_DISPONIBILIDAD", [ $fecha, $oficinaID, 0] ) ;
        return $disponibility;
    }

    public function auditorioprecio( $localID, $modeloID, $planID ){
        return OficinaPromocion::where( 'local_id', $localID)->where( 'modelo_id', $modeloID)->where( 'plan_id', Auth::user()->plan_id)->where( 'tipo', "H")->where( "desde", "<=", 0 )->first();        
    }

    public function reservaById( $id ){
        return Reserva::where("id", $id)->where("empresa_id", Auth::user()->id)->first();
    }
    

    public function create( $v ) {

    	if(!isset($v['proyector'])){
    		$v['proyector'] = 'NO';
    	}

    	if(!isset($v['cochera_id'])){
    		$v['cochera_id'] = 1;
    	}

    	if(!isset($v['placa'])){
    		$v['placa'] = "";
    	}

    	if(!isset($v['movil'])){
    		$v['movil'] = "N";
    	}

        if(!isset($v["observacion"]) || empty($v["observacion"])){
            $v["observacion"] = "[]";
        } else {
            $v["observacion"] = json_encode([[
                'usuario' => Auth::user()->nombre,
                'body' => $v["observacion"],
                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]]);
        }

		$reserva = ( new SessionRepo )->CallRaw("mysql", "AL_RESERVA_CREATE", [ 
			$v["fecha"], 
			$v["oficina_id"],
			$v["hini"], 
			$v["hfin"], 
			$v["empresa_id"],
			$v["proyector"], 
			$v["cochera_id"],
			$v["placa"], 
			$v["movil"], //NO ANDROID IPHONE
			$v["observacion"], 
			\Auth::user()->empresa_nombre,
            isset($v["nombre"]) ? $v["nombre"] : "",
            isset($v["silla"])  ? $v["silla"]  : "0",
            isset($v["mesa"])   ? $v["mesa"]   : "N",
            isset($v["audio"])  ? $v["audio"]  : "N",
            isset($v["cupon"])  ? $v["cupon"]  : ""
		]);
        if( $reserva[0]["id"] > 0){
            $montoExtras = 0;
            if( isset( $v["detalle"] ) && count( $v["detalle"] ) > 0 ){
                foreach( $v["detalle"] as $det ){
                    //INSERT RESERVA DETALLE
                    ReservaDetalle::create(
                        array(
                            'reserva_id'    => $reserva[0]["id"], 
                            'concepto_id'   => $det["concepto"], 
                            'precio'        => $det["precio"], 
                            'cantidad'      => $det["cantidad"], 
                            'created_at'    => date("Y-m-d H:i:s")
                        )
                    );
                    $montoExtras = $montoExtras + ( $det["cantidad"] * $det["precio"] );
                }
            }

            if( isset( $v["estructura"] ) && count( $v["estructura"] ) > 0 ){
                ( new AsistenciaRepo )->massive( $reserva[0]["id"], $v["estructura"] );
            }

            // Enviar correo
            Mail::send(new \CVClient\Mail\ReservaMail($reserva[0]['id']));


            $oficina = Oficina::find($v['oficina_id']);

            // Enviar notificacion
            if($oficina->modelo_id != 1){
                $this->sendWSMessage("E", [
                    "from" => Auth::user()->empresa_nombre,
                    "empresa_id" => Auth::user()->id,
                    "message" => "Reserva ". $oficina->nombre . " capacidad " . $oficina->capacidad. " piso " . $oficina->piso_id . " para el " . $v['fecha'] . " de ".$v['hini']. " a " . $v['hfin'],
                    "module" => "reserva",
                    "created_at" => date('d/m/y H:i')
                ]);                
            }
        }

		return $reserva[0];
    }

    public function search( $fecha, $getparams ){
        $getparams["fecha"] = $fecha;
        $getparams["empresa_id"] = \Auth::user()->id;
        return ( new QueryRepo )->Q_reserva( $getparams );
    }


    public function getInviteList( $reserva_id ){
        return ( new QueryRepo )->Q_invitado( [ "reserva_id" => $reserva_id] );
    }



    public function cancel( $params ) {
        $return = (new SessionRepo)->CallRaw("mysql", "AL_RESERVA_CANCEL_CLIENTE", [$params["reserva_id"],Auth::user()->empresa_nombre]);
        if($return[0]['load'] == 1){
            Mail::send(new \CVClient\Mail\ReservaMail($params['reserva_id']));
        }
        return $return;
    }
}
?>