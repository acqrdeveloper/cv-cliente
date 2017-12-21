<?php
namespace CVClient\CV\Repos;
use CVClient\CV\Models\Cochera;
use CVClient\Common\Repos\SessionRepo;
use CVClient\Common\Repos\QueryRepo;
class CocheraRepo{
	public function disponibility( $reservaID, $fReserva, $localID, $vhini, $vhfin ){
		$disponibility = ( new SessionRepo )->CallRaw("mysql", "AL_COCHERA_DISPONIBILIDAD", [ $reservaID, $fReserva, $localID, $vhini, $vhfin ] ) ;
		return $disponibility;
	}
}
?>