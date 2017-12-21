<?php namespace CVClient\Http\Controllers\CV;
header('content-type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
use CVClient\Http\Controllers\Controller;
use CVClient\Common\Repos\MobileRepo;
class mobileController extends Controller {
	public function funcion($funcion){
		try {
			$params = request()->all();
			return response()->json( ( new MobileRepo )->funcion( $funcion, $params ), 200);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage(), 'line'=>$e->getLine() , 'file'=>$e->getFile()], 412);
		}
	}

	public function setPushToken($id){
		try {
			\DB::table('empresa')->where('id', $id)->update(['pushwoosh'=>request()->input('token')]);
			return response()->json(['message'=>'Token updated']);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}

}