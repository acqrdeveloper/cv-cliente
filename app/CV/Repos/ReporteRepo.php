<?php
namespace CVClient\CV\Repos;
use CVClient\Common\Repos\QueryRepo;
use CVClient\Common\Repos\ExportRepo;
class ReporteRepo
{
    public function reporte( $tipo, $params )
    {
    	$empresa = \Auth::user();
    	$function = "";
		$params["empresa_id"] = \Auth::user()->id;
		switch ($tipo) {
		    case "cdr":
		    	$params["userfield"] = trim( \Auth::user()->preferencia_cdr );
		    	$params["userfield"] == '' ? $params["userfield"] = '-' : $params["userfield"];
		        $function = "Q_cliente_reporte_cdr";
		        break;
		    case "correspondencia":
		        $function = "Q_correspondencia";
		        break;
		    case "recado":
		        $function = "Q_recado";
		        break;
		    case "factura":
		        $function = "Q_facturacion";
		        break;
		    case "factura_item":
		    	$function = "Q_cliente_factura_item";
		        break;
		    case "invitados":
		    	$function = "Q_reporte_invitados";
		        break;
		}
		if( $function != "" ){
        	$data = call_user_func_array( array( ( new QueryRepo ) ,$function ), array( $params ) );
		}else{
			$data = null;
		}
		return $data;
    }

    public function export( $tipo, $params )
    {
    	$function = "";
    	$empresa = \Auth::user();
		$params["empresa_id"] = \Auth::user()->id;
		switch ($tipo) {
		    case "cdr":
		    	$params["userfield"] = trim( \Auth::user()->preferencia_cdr );
		    	$params["userfield"] == '' ? $params["userfield"] = '-' : $params["userfield"];
		        $function = "cliente_reporte_cdr";
		        break;
		    case "correspondencia":
		        $function = "correspondencia";
		        break;
		    case "recado":
		        $function = "recado";
		        break;
		    case "factura":
		        $function = "factura";
		        break;
		    case "invitados":
		    	$function = "invitados";
		    case "invitado":
		    	$function = "invitado";
		        break;
		}
        ( new ExportRepo )->createCSV( $function, $params );
    }
}