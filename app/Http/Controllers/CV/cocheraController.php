<?php namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\CocheraRepo;
class cocheraController extends Controller {
    public function disponibility( $reservaID, $fReserva, $localID, $vhini, $vhfin ){
        try{
            return ( new CocheraRepo )->disponibility( $reservaID, $fReserva, $localID, $vhini, $vhfin );
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
    }
    
}