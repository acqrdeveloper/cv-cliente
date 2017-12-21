<?php namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\CV\Repos\EmpresaRepo;
use CVClient\CV\Repos\CentralRepo;
use CVClient\CV\Repos\SessionRepo;
use Curl\Curl;
use CVClient\Http\Controllers\AppAuth;

class centralController extends Controller {

	public $user = null;
	public $params = null;

	public function __construct(Request $req){
		$this->user = (new AppAuth)->getAuth();
		$this->params = $req->all();
	}

	public function getPbx(){
		try {
			$curl = new Curl();
			$curl->get('http://noc.ngalax.com/pbx/' . $this->user->id);

			if ($curl->error) {
				throw new \Exception($curl->errorMessage);
			} else {
				return response()->json( json_decode(json_encode($curl->response), true));
			}
		} catch (Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}


	public function addOption(){
		try {
			( new CentralRepo )->newOption( request()->all() );
			return response()->json(['message'=>'Opción creada', 'edit'=>false]);
		} catch (\Exception $e) {
			return response($e->getMessage(), 412);
		}
	}

	public function assignCdr($empresa_id){
		try {
			$params = request()->all();
			$params['empresa_id'] = $empresa_id;
			(new EmpresaRepo)->assignCdr($params);
			return response()->json(['message'=>'Cdr actualizado.']);
		} catch (\Exception $e) {
			return response($e->getMessage(), 412);
		}
	}

	public function deleteOption($central_id, $id){
		try {
			( new CentralRepo )->deleteOption($params);
			return response()->json(['message'=>'Opción eliminada.']);
		} catch (\Exception $e) {
			return response($e->getMessage(), 412);
		}
	}

	/*
	public function getPbx($empresa_id){
		try {
			return response()->json( (new CentralRepo)->getByCompanyId($empresa_id) );
		} catch (\Exception $e) {
			return response($e->getMessage(), 412);
		}
	}*/

	public function updateConfig(){
		try {
			$response = ( new CentralRepo )->updateConfig(request()->all());
			$response['message'] = 'Central actualizada.';
			return response()->json($response);
		} catch (\Exception $e) {
			return response($e->getMessage(), 412);
		}
	}

	public function updateOption(){
		try {
			( new CentralRepo )->updateOption(request()->all());
			return response()->json(['message'=>'Opción actualizada.']);
		} catch (\Exception $e) {
			return response($e->getTraceAsString(), 412);
		}
	}
}