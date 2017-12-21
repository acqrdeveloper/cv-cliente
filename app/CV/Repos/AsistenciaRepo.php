<?php
namespace CVClient\CV\Repos;
use CVClient\CV\Models\Reserva;
use CVClient\CV\Models\ReservaInvitado;
use CVClient\Common\Repos\QueryRepo;
class AsistenciaRepo{

	public function massive( $reserva_id, $estructura ){
		$return  = [];
		$reserva = Reserva::where( "id", $reserva_id )->first();#existe reserva
		//print_r( $reserva );
		//print_r( "-+-" );
		if( !empty( $reserva ) ){
			if( strtotime( $reserva["fecha_reserva"]." ".$reserva["hora_inicio"] ) > strtotime( date( "Y-m-d H:i:s" ) ) ){#permite subir otra lista mientras el evento aun no inicie
				foreach( $estructura as $params ){
					$subreturn = $this->create( $reserva_id, $params );
					array_push( $return, $subreturn );
				}
			}
		}
		//print_r( strtotime( $reserva["fecha_reserva"]." ".$reserva["hora_inicio"] ) );
		//print_r( "-+-" );
		//print_r( strtotime( date( "Y-m-d H:i:s" ) ) );
		//print_r( "-+-" );
		//print_r( $return );
		return $return;
	}

	public function getInviteList( $reserva_id ){
		return ReservaInvitado::where( "reserva_id", $reserva_id )->get();
	}


	public function getInviteTotal( $reserva_id ){
		return ReservaInvitado::where( "reserva_id", $reserva_id )->groupBy("reserva_id")->select( \DB::raw('SUM(asistencia) as asistencia, SUM(nuevo) as nuevo, COUNT(*) as total'))->first();
	}

	public function create( $reserva_id, $params, $nuevo = 0 ){
		$return = [];		
		//print_r( $params );
		if( isset( $params["dni"] ) && $params["dni"] != "" && strlen( $params["dni"] ) == 8 ){
			$exist  = ReservaInvitado::where( "reserva_id", $reserva_id  )->where( "dni", $params["dni"] )->first();
			//print_r( $exist );
			if( empty( $exist ) ){
				$return = ReservaInvitado::create(
					array(
						'reserva_id' 	=> $reserva_id,
						'dni' 			=> $params["dni"],
						'nomape' 		=> isset( $params["nomape"] ) ? $params["nomape"] : "",
						'email' 		=> isset( $params["email"] )  ? $params["email"]  : "",
						'movil' 		=> isset( $params["movil"] )  ? $params["movil"]  : "",
						'created_at' 	=> date("Y-m-d H:i:s"),
						'updated_at' 	=> date("Y-m-d H:i:s"),
						'estado' 		=> 'A',
						'asistencia' 	=> $nuevo,
						'nuevo' 		=> $nuevo
					)
				);
			}
		}
		return $return;
	}

	public function asistencia( $reserva_id, $dni ){
		$return = ReservaInvitado::where( 'reserva_id', $reserva_id )->where( 'dni', $dni )->update(
			array(
				'updated_at' => date("Y-m-d H:i:s"),
				'asistencia' => 1
			)
		);
		return $return;
	}

	public function delete( $reserva_id, $dni ){
		$return = ReservaInvitado::where( 'reserva_id', $reserva_id )->where( 'dni', $dni )->delete();
		return $return;
	}
}
?>