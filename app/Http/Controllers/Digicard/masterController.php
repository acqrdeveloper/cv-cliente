<?php namespace CVClient\Http\Controllers\Digicard;

use CVClient\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CVClient\Http\Controllers\AppAuth;

use CVClient\CV\Models\Digicard;
use CVClient\CV\Models\DigicardProducto;
use CVClient\CV\Models\Local;

class masterController extends Controller {

	public $user = null;
	public $params = null;

	public function __construct(Request $req){

		try {
			$this->user = (new AppAuth)->getAuth();			
		} catch (\Exception $e) {
		}

		$this->params = $req->all();
	}

	public function setInfo(){

		//var_dump($this->params['user_file']);
		try {

			$digicard = Digicard::updateOrCreate(['empresa_id'=>$this->user->id], $this->params);

			$resp = ['load'=>true];

			try {
				// Save user image file
				if(isset($this->params['user_file'])){

					$info = getimagesize($this->params['user_file']);

					if(is_array($info) && ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/jpg' || $info['mime'] == 'image/png')){

						$b64 = explode(";", $this->params['user_file']);

						$content = str_replace("base64,", "",  $b64[ count($b64)-1] );

						file_put_contents( public_path('images/digicard') . "/" . "e".$this->user->id, base64_decode($content) );
					} else {
						$resp['obs'] = 'El archivo no es una imagen válida';
					}

				} else {
					$resp['obs'] = 'No se ha cargado una imagen';
				}	
			} catch (\Exception $e) {
				\Log::error($e);
				$resp['obs'] = 'El archivo no es una imagen';
			}

			return response()->json($resp);

		} catch (\Exception $e) {
			\Log::error($e);
			return response()->json(['load'=>false, 'message'=>'Hubo un error al guardar la información.'], 412);
		}
	}

	public function createProduct(){

		try {
			$p = $this->params;
			$p['empresa_id'] = $this->user->id;

			$producto = DigicardProducto::updateOrCreate(['id'=>isset($p['id'])?$p['id']:0], $p);

			$resp = ['load'=>true];

			try {
				// Save user image file
				if(isset($this->params['product_file']) && !is_null($this->params['product_file'])){

					$info = getimagesize($this->params['product_file']);

					if(is_array($info) && ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/jpg' || $info['mime'] == 'image/png')){

						$b64 = explode(";", $this->params['product_file']);

						$content = str_replace("base64,", "",  $b64[ count($b64)-1] );

						file_put_contents( public_path('images/digicard') . "/" . "p".$producto->id, base64_decode($content) );
					} else {
						$resp['obs'] = 'El archivo no es una imagen válida';
					}

				} else {
					$resp['obs'] = 'No se ha cargado una imagen';
				}
			} catch (\Exception $e) {
				\Log::error($e);
				$resp['obs'] = 'El archivo no es una imagen';
			}

			return response()->json($resp);

		} catch (\Exception $e) {
			\Log::error($e);
			return response()->json(['load'=>false, 'message'=>'Hubo un error al guardar el producto.'], 412);
		}
	}

	public function deleteProduct(){

		try {

			$p = $this->params;
			$p['empresa_id'] = $this->user->id;

			$producto = DigicardProducto::where('id',$p['id'])->delete();

			$resp = ['load'=>true];
			// Save product image file
			if( file_exists(public_path('images/digicard').'/p'.$p['id']) ){
				unlink(public_path('images/digicard').'/p'.$p['id']);
			}

			return response()->json($resp);

		} catch (\Exception $e) {
			\Log::error($e);
			return response()->json(['load'=>false, 'message'=>'Hubo un error al guardar el producto.']);
		}
	}

	public function getInfo(){

		try {
			
			$resp = $this->getDigicard();

			return response()->json($resp);

		} catch (\Exception $e) {
			\Log::error($e);
			return response()->json(['load'=>false, 'message'=>'Hubo un error al obtener la información de la digicard.'], 412);
		}
	}

	public function getById($empresa_id){
		try {
			$this->user = (object)['id'=>$empresa_id];
			$data = (object)$this->getDigicard();

			if(!$data->has_digicard)
				throw new \Exception("El usuario no tiene configurado la digicard");

			return view('digicard.preview', ['data'=>$data]);
		} catch (\Exception $e) {
			return response('',404);
		}
	}

	public function getDigicard(){

		$digicard = Digicard::find($this->user->id);

		$resp = [];

		if( !is_null($digicard) ){
			$resp = $digicard->toArray();
			$resp['has_digicard'] = true;

			if(!is_null($resp['local_id']) && $resp['local_id'] > 0)
				$resp['local'] = Local::where('id', $resp['local_id'])->first(['latitud','longitud','direccion','distrito2']);

			$resp['productos'] = DigicardProducto::findByCompany($this->user->id);
		} else {
			$resp['empresa_id'] = $this->user->id;
			$resp['has_digicard'] = false;
		}

		return $resp;
	}
}