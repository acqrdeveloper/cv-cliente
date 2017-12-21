<?php
namespace CVClient\CV\Repos;
use CVClient\CV\Models\CLocal;
use CVClient\CV\Models\Correspondencia;
use CVClient\CV\Models\CorrespondenciaHistorial;
use CVClient\Common\Repos\SessionRepo;
use CVClient\Common\Repos\QueryRepo;

class CorrespondenciaRepo{
	public function search( $anio, $mes, $estado, $getparams ){
		$getparams["anio"] = $anio;
		$getparams["mes"] = $mes;
		$getparams["estado"] = $estado;
		return ( new QueryRepo )->Q_correspondencia( $getparams );
	}

    public function report( $anio, $mes, $getparams ){
        $getparams["anio"] = $anio;
        $getparams["mes"] = $mes;
        return ( new QueryRepo )->Q_correspondenciaEmpresa( $getparams );
    }

    public function history( $corresID ){
    	$history = CorrespondenciaHistorial::where( "correspondencia_id", $corresID )->get();
    	return $history;	
    }

}
?>