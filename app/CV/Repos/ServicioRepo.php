<?php
namespace CVClient\CV\Repos;
ini_set('max_execution_time', 3000);
use CVClient\CV\Models\RecursoPeriodo;
use CVClient\CV\Models\Empresa;
use CVClient\CV\Models\EmpresaServicio;
use CVClient\CV\Models\FacturaTemporal;
use CVClient\CV\Repos\EmpresaRepo;
use CVClient\CV\Repos\FacturaRepo;
use CVClient\Common\Repos\QueryRepo;
class ServicioRepo
{
    public function getRecursoPeriodo( $empresa_id, $anio, $mes = 0 )
    {
        return (new QueryRepo)->Q_recurso_periodo([ "empresa_id" => $empresa_id, "anio" => $anio, "mes" => $mes ]);
    }

    public function setEmpresaServicio( $empresa_id, $params ){
        return EmpresaServicio::create(
            array(
                'servicio_extra_id'     => isset( $params["servicio_extra_id"] ) ? $params["servicio_extra_id"] : 0,
                'empresa_id'            => $empresa_id,
                'mes'                   => isset( $params["mes"] ) ? $params["mes"] : -1,
                'tipo'                  => $params["tipo"],
                'monto'                 => $params["monto"],
                'concepto'              => isset( $params["concepto"] ) ? $params["concepto"] : ""
            )
        );
    }
}