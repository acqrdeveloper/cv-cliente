<?php namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\ReporteRepo;
class reporteController extends Controller
{
    public function reporte( $tipo )
    {
        try{
            $params = request()->all();
            return response()->json( ( new ReporteRepo )->reporte( $tipo, $params ) );
        } catch(\Exception $ex) {
            return response()->json( [ "load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412 );
        }
    }

    public function export( $tipo )
    {
        try{
            $params = request()->all();
            if( isset( $params["limite"] ) ){
            	unset( $params["limite"] );
            }
            ( new ReporteRepo )->export( $tipo, $params );

        } catch(\Exception $ex) {
            return response()->json( [ "load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412 );
        }    	
    }
}