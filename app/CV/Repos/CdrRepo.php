<?php
namespace CVClient\CV\Repos;
//use CVClient\CV\Models\CorrespondenciaHistorial;
use CVClient\Common\Repos\SessionRepo;
use CVClient\Common\Repos\QueryRepo;

class CdrRepo{

    public function report( $anio, $mes, $getparams ){
        $getparams["anio"] = $anio;
        $getparams["mes"] = $mes;
        return ( new QueryRepo )->Q_cdrEmpresa( $getparams );
    }
}
?>