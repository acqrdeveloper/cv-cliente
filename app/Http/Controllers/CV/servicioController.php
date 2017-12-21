<?php
namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use CVClient\CV\Repos\ServicioRepo;
use Illuminate\Http\Request;
class servicioController extends Controller
{
    public function getRecursoPeriodo( $empresa_id, $anio, $mes )
    {
        try {
            return response()->json( ( new ServicioRepo )->getRecursoPeriodo( $empresa_id, $anio, $mes ) , 200);
        } catch (\Exception $e) {
            return response()->json(['message'=>$e->getMessage()], 412);
        }
    }

}