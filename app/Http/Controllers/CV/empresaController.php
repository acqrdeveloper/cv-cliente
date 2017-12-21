<?php namespace CVClient\Http\Controllers\CV;
use CVClient\Http\Controllers\Controller;
use CVClient\User;
use CVClient\CV\Models\CLocal;
use CVClient\CV\Repos\EmpresaRepo;
use CVClient\CV\Repos\FacturaRepo;
use Illuminate\Http\Request;
use Curl\Curl;
use Carbon\Carbon;

class empresaController extends Controller {


	public function changeLogin(){
		try {
			$params = request()->all();
			(new EmpresaRepo)->changeLogin( \Auth::user()->id, $params );
			return response()->json(['message'=>'Datos actualizados.']);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}

	public function changePassword(){
		try {
			$params = request()->all();
			(new EmpresaRepo)->changePassword( \Auth::user()->id, $params );
			return response()->json(['message'=>'Contraseña actualizada']);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}

	public function changeFacturaInfo(){
		try {
			$params = request()->all();
			(new EmpresaRepo)->changeFacturaInfo( \Auth::user()->id, $params );
			return response()->json(['message'=>'Datos actualizados']);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}

	public function register(){
		\DB::beginTransaction();
		try {
			$params = request()->all();

			$repo  = new EmpresaRepo();

			// ver si ya existe un registro con el ruc o dni
			$emp = $repo->findByNID($params['ruc']);

			if(!is_null($emp)){
				// La empresa existe, por tanto no se puede registrar
				throw new \Exception("El DNI o RUC ingresado ya esta registrado");
			}

			// Preparando los datos
			$p = [
				'plan_id' 							=> 31,
				'preferencia_facturacion' 			=> 'MENSUAL',
				'preferencia_fiscal' 				=> 'NO',
				'preferencia_fiscal_nro_partida' 	=> '',
				'preferencia_comprobante' 			=> 'BOLETA',
				'preferencia_login' 				=> $params['email'],
				'empresa_nombre' 					=> '',
				'empresa_ruc' 						=> $params['ruc'],
				'empresa_direccion' 				=> '',
				'empresa_rubro' 					=> '',
				'nombre_comercial' 					=> '',
				'fac_nombre'						=> $params['nombre'],
				'fac_apellido'						=> '',
				'fac_dni'							=> '',
				'fac_email'							=> $params['email'],
				'fac_telefono'						=> $params['telefono'],
				'fac_celular'						=> $params['telefono'],
				'fac_domicilio'						=> '',
				'asesor'							=> '',
				'promo'								=> 'N',
				'convenio'							=> 'N',
				'convenio_duration'					=> 0,
				'facebook_id'						=> $params['facebook_id'],
				'google_id'							=> $params['google_id'],
				'convenio_duration'					=> 0,
				'representante' => [
					'nombre' => $params['nombre'],
					'apellido' => '',
					'dni' => '',
					'correo' => $params['email'],
					'telefonos' => $params['telefono'],
					'domicilio' => ''
				],
				'contrato' => [
					'fecha_inicio' => Carbon::now()->format('Y-m-d'),
					'fecha_fin' => Carbon::now()->addMonths(6)->format('Y-m-d')
				],
				'servicios_extras' => [[
					'servicio_extra_id' => 1,
					'mes' => -1,
					'tipo' => 'P',
					'monto' => 0,
					'CONCEPTO' => 'EXTERNO'
				]]
			];

			// Obtener los datos del ruc, si es ubicable
			if(strlen($params['ruc'])==11){
				$sunat = $this->searchInSunat($params['ruc']);
				if($sunat->rsocial == 'DATOS NO ENCONTRADOS'){
					throw new \Exception("El número RUC ingresado no existe");
				}

				/*
				echo "Sunat" . PHP_EOL;
				dd($sunat);*/

				$p['empresa_nombre'] = $sunat->rsocial;
				$p['empresa_direccion'] = $sunat->direccion;
				$p['representante']['domicilio'] = $sunat->direccion;

				/*
				echo "DATOS" . PHP_EOL;
				dd($p);*/
			}

			// Crear empresa y envia un mail de bienvenida
			$return = $repo->create($p);

			\DB::commit();

			// create session
			session(['userCreated'=>$return['empresa']['id']]);

			$response = ['load'=>true];

			if(isset($params['pbx']) && $params['pbx'] == 'on'){
				try {
					$curl = new Curl();
					
					$curl->post('http://pbx.centrosvirtuales.com/pbx', [
						'customer_id' => $return['empresa']['id'],
						'prefix' => 'cv',
						'pbx' => [
							['label'=>'101', 'redirect_to'=>$params['telefono']]
						]
					]);

					if ($curl->error) {
						$response['pbx_obs'] = 'No se pudo crear la central';
						//return response()->json( json_decode( json_encode($curl->response), true), 412);
					} else {
						$response['pbx'] = json_decode( json_encode($curl->response), true);
					}	
				} catch (\Exception $e) {
					\Log::error($e);
					$response['pbx_obs'] = 'No se pudo crear la central';
				}
			}

			if(isset($params['local_id']) && $params['local_id']>0){
				$response['local_direccion'] = CLocal::find($params['local_id'])->direccion;
			}

			//\Auth::login( User::find($return['empresa']['id']) );
			// Retornar vacío
			return response()->json($response, 200);

		} catch (\Exception $e) {
			\DB::rollBack();
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}

	public function registerPbx(){
		try {

			$empID = session('userCreated', 0);

			if($empID < 1)
				throw new \Expception("No tenemos una empresa configurada");

			$params = request()->all();

			if(!isset($params['ext']) || !isset($params['celular']))
				throw new \Expception("Se necesitas los anexos y celulares para configurar la central");

			$pbx = [];

			for($i=0;$i<count($params['ext']);$i++){
				$pbx[$i]['label'] = $params['ext'][$i];
				$pbx[$i]['redirect_to'] = $params['celular'][$i];
			}

			
			if(count($pbx) < 1)
				throw new \Exception("Debe haber al menos 1 anexo para configurar la central");

			$curl = new Curl();
			$curl->post('http://pbx.centrosvirtuales.com/pbx', [
				'customer_id' => $empID,
				'prefix' => 'cv',
				'pbx' => $pbx
			]);

			if ($curl->error) {
				return response()->json( json_decode( json_encode($curl->response), true), 412);
			} else {
				return response()->json( json_decode( json_encode($curl->response), true), 200);
			}

		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}

	private function searchInSunat($ruc){
		$curl = new Curl();
		$curl->get("http://service.facturame.pe/consultaruc/" . $ruc);
		if ($curl->error) {
		   throw new \Exception('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
		} else {
		    $d = $curl->response;
		    $curl->close();
		    return $d;
		}
	}
}