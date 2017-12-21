<?php namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\CuponRepo;

class cuponController extends Controller {
	public function valid( $codigo ){
        try{
            return response()->json(( new CuponRepo )->valid( $codigo ));
        } catch(\Exception $ex) {
            return response()->json(["load" => true, "error" => $ex->getMessage(), "detail" => $ex->getLine() ], 412);
        }
	}
}