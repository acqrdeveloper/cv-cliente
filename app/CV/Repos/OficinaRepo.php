<?php
namespace CVClient\CV\Repos;
use CVClient\CV\Models\Oficina;
use CVClient\Common\Repos\SessionRepo;
use CVClient\Common\Repos\QueryRepo;

class OficinaRepo{
	public function disponibility( $fecha, $oficinaID ){
		$disponibility = ( new SessionRepo )->CallRaw("mysql", "AL_OFICINA_DISPONIBILIDAD", [ $fecha, $oficinaID] ) ;
		return $disponibility;
	}
	public function getOficinaList( $param ){
		$oficinas = Oficina::where( "local_id", $param["local_id"] )->where( "modelo_id", $param["modelo_id"] )->where( "estado", "A" )->get();
		return $oficinas;
	}
}
?>