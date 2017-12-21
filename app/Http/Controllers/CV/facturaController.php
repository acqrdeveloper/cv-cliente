<?php namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\FacturaRepo;

class facturaController extends Controller {

    public function search( Request $request )
    {
        try{
            $getparams = $request->all();
            return response()->json(( new FacturaRepo )->search( $getparams ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }
    
    public function getone($factura_id)
    {
        try{
            return response()->json(( new FacturaRepo )->getone( $factura_id ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function factura_item($factura_id)
    {
        try{
            return response()->json( ( new FacturaRepo )->factura_detalle( $factura_id ) );
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function factura_historial($factura_id)
    {
        try{
            return response()->json( ( new FacturaRepo )->factura_historial( $factura_id ) );
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }



    public function report_pagos( $anio, $mes, Request $request )
    {
        try{
            $getparams = $request->all();
            return response()->json(( new FacturaRepo )->report_pagos( $anio, $mes, $getparams ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function report_facturacion( $anio, $mes, Request $request )
    {
        try{
            $getparams = $request->all();
            return response()->json(( new FacturaRepo )->report_facturacion( $anio, $mes, $getparams ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function facturacion_empresas( $anio, $mes, $ciclo )
    {
        try{
            return response()->json(( new FacturaRepo )->facturacion_empresas( $anio, $mes, $ciclo ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function comprobantepdf( $receptor_ruc, $documento, $serie, $numero )
    {
        $pdf = ( new FacturaRepo )->comprobantePDF( $receptor_ruc, $documento, $serie, $numero );
        if( $pdf["load"] ){
            $pdf = $pdf["data"]->setPaper('a4', 'portrait')->setWarnings(false)->stream();
        }
        return $pdf;
    }

    public function comprobantepdfdownload( $receptor_ruc, $documento, $serie, $numero )
    {

        $pdf = ( new FacturaRepo )->comprobantePDF( $receptor_ruc, $documento, $serie, $numero );
        if( $pdf["load"] ){
            $pdf = $pdf["data"]->setPaper('a4', 'portrait')->setWarnings(false)->download();
        }
        return $pdf;
    }

    public function payment_detail($factura_id)
    {
        try{
            return response()->json(( new FacturaRepo )->payment_detail( $factura_id ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

    public function nota_search(Request $request){
        try{
            $getparams = $request->all();
            return response()->json(( new FacturaRepo )->nota_search( $getparams ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }

}