<?php
namespace CVClient\CV\Repos;
use CVClient\CV\Models\Recado;
use CVClient\CV\Models\RecadoHistorial;
use CVClient\Common\Repos\SessionRepo;
use CVClient\Common\Repos\QueryRepo;
use CVClient\CV\Repos\EmpresaRepo;

class RecadoRepo{
	public function search( $anio, $mes, $estado, $getparams )
    {
		$getparams["anio"] = $anio;
		$getparams["mes"] = $mes;
		$getparams["estado"] = $estado;
		return ( new QueryRepo )->Q_recado( $getparams );
	}

    public function getById( $id )
    {
        return Recado::where("id",$id)->first();
    }

    public function history( $recadoID ){
    	$history = RecadoHistorial::where( "recado_id", $recadoID )->get();
    	return $history;	
    }

}
?>