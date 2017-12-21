<?php namespace CVClient\CV\Repos;

use Mail;
use CVClient\Common\Repos\QueryRepo;
use CVClient\Common\Repos\SessionRepo;
use CVClient\CV\Models\Plan;
use CVClient\CV\Models\Contrato;
use CVClient\CV\Models\Digicard;
use CVClient\CV\Models\Empresa;
use CVClient\CV\Models\RecursoPeriodo;
use CVClient\CV\Models\EmpresaServicio;

// Repos
use CVClient\CV\Repos\FacturaRepo;
use CVClient\CV\Repos\RepresentanteRepo;
use CVClient\CV\Repos\ServicioRepo;

class EmpresaRepo {

    public function create( $var ){
    	$return = [ "empresa" => [], "factura" => [], "representante" => [], "servicio" => [], "contrato" => [], "mail" => [], "ndoc" => [] ];

    	$pass = str_random(8);
    	$return["mail"] = Empresa::where( "preferencia_login", $var["preferencia_login"] )->first();

    	if(!empty($return["mail"]))
    		throw new \Exception("El correo electrónico " . $var["preferencia_login"] . " ya está ingresado.");

    	$return["ndoc"] = Empresa::where( "empresa_ruc", $var["empresa_ruc"] )->first();
    	if(!empty($return["ndoc"]))
    		throw new \Exception("El número de documento " . $var["empresa_ruc"] . " ya está ingresado.");

    	$hash = \Hash::make( $pass );

    	$empresa = Empresa::create(
    		array(
				'plan_id'							=> 31,
				'central_id' 						=> 0,
				'central' 							=> 'N',
				'preferencia_estado'				=> 'A',
				'preferencia_login'					=> $var["preferencia_login"],
				'preferencia_contrasenia'			=> $pass,
				'preferencia_facturacion'			=> date("d") > 14 ? 'QUINCENAL' : 'MENSUAL',
				'preferencia_cdr'					=> '',
				'preferencia_fiscal'				=> 'NO',
				'preferencia_fiscal_nro_partida'	=> '',
				'preferencia_comprobante'			=> substr( $var["empresa_ruc"], 0, 2 ) == '20' ? 'FACTURA' : 'BOLETA',

				'empresa_nombre' 					=> $var["empresa_nombre"],
				'empresa_ruc' 						=> $var["empresa_ruc"],
				'empresa_direccion' 				=> isset($var["empresa_direccion"]) ? $var["empresa_direccion"] : "",
				'empresa_rubro' 					=> isset($var["empresa_rubro"]) 	? $var["empresa_rubro"] : "",
				'nombre_comercial' 					=> isset($var["nombre_comercial"]) 	? $var["nombre_comercial"] : "",
				'url_web'  							=> isset($var["url"]) ? $var["url"] : "",

				'fac_nombre'						=> isset($var["fac_nombre"]) 	? $var["fac_nombre"] : $var["empresa_nombre"],
				'fac_apellido'						=> isset($var["fac_apellido"]) 	? $var["fac_apellido"] : "",
				'fac_dni'							=> isset($var["fac_dni"]) 		? $var["fac_dni"] : "",
				'fac_email'							=> isset($var["preferencia_login"]) 	? $var["preferencia_login"] : "",
				'fac_telefono'						=> isset($var["fac_telefono"]) 	? $var["fac_telefono"] : "",
				'fac_celular'						=> isset($var["fac_celular"]) 	? $var["fac_celular"] : "",
				'fac_domicilio'						=> isset($var["fac_domicilio"]) ? $var["fac_domicilio"] : "",
				'facebook_id'						=> isset($var["facebook_id"]) ? $var["facebook_id"] : "",
				'google_id'							=> isset($var["google_id"]) ? $var["google_id"] : "",

				'moneda' 							=> 'S',
				'asesor'							=> 'Sistema',
				'fecha_creacion'					=> date("Y-m-d H:i:s"),
				'updated_at'						=> date("Y-m-d H:i:s"),
				'promo' 							=> 'N',
				'flag_encuesta' 					=> 'N',
				'convenio'							=> 'N',
				'convenio_duration'					=> 0,
				'carrera'							=> 'N',
				'password'							=> $hash,
				'api_token' 						=> $hash
    		)
    	);
    	$return["empresa"] = $empresa;

	    if( isset( $var['representante'] ) ){
	    	$rep = $var['representante'];
	    	$rep["empresa_id"] = $empresa->id;
	    	$rep["is_login"] = "S";
	    	(new RepresentanteRepo)->create( $rep );
	    	$return["representante"] = $rep;
	    }

	    // Para crear 
	    if(isset($var['local_id']) && $var['local_id']>0){
	    	Digicard::create([
	    		'id' => $empresa->id,
	    		'local_id' => $var['local_id']
	    	]);
	    }

	    try {
	        Mail::send(new \CVClient\Mail\Credentials($empresa));	    	
	    } catch (\Exception $e) {
	    	\Log::error($e);
	    	$return['mail']='No se pudo enviar las credenciales';
	    }

    	return $return;
    }

	public function register_uniqueValidate($p){
		$return = [ "email" => [], "empresa_ruc" => [] ];
		$return["email"]       = $this->findByLogin($p["preferencia_login"]);
		$return["empresa_ruc"] = $this->findByNID($p["empresa_ruc"]);
		return $return;
	}

	public function findByLogin($preferencia_login){
		return Empresa::where('preferencia_login', $preferencia_login)->first();
	}

	public function findByNID($ruc){
		return Empresa::where('empresa_ruc', $ruc)->first();
	}

	public function getEmpresaRecursoPeriodoHoras( $empresa_id, $ciclo, $anio, $mes )
	{
		/*
		$anio = date("Y");
		$mes  = date("m");
		/
		if( $ciclo == 'QUINCENAL' ){
			if( date("d") < 15 ){
				$anio = date( "Y", strtotime("-1 months", strtotime( date( "Y-m-d" ) ) ) );
				$mes  = date( "m", strtotime("-1 months", strtotime( date( "Y-m-d" ) ) ) );
			}
		}
		*/
		$recurso = RecursoPeriodo::where( "empresa_id", $empresa_id )->where( "anio", $anio )->where( "mes", $mes )->orderByRaw(\DB::raw('anio DESC, mes DESC'))->first();
		return $recurso;
	}


	public function getEmpresaPlan( $plan_id )
	{
		return Plan::where( "id", $plan_id )->first();
	}

	private function updateEmpresa( $empresa_id, $params ){
		return Empresa::where( "id", $empresa_id )->update( $params );
	}

	public function changeLogin( $empresa_id, $params ){
		if( filter_var( $params['preferencia_login'], FILTER_VALIDATE_EMAIL ) ) {
			$uniquemail = Empresa::where( "preferencia_login", $params["preferencia_login"] )->first();
			if( empty( $uniquemail ) ){
				$this->updateEmpresa( 
					$empresa_id, 
					array(
						'preferencia_login' => $params["preferencia_login"]
					) 
				);
			}else{
				throw new \Exception("Email en uso");
			}
		} else {
			throw new \Exception("Estructura de Email Invalido");
		}
	}

	public function changePassword( $empresa_id, $params ){
		if( \Auth::user()->preferencia_contrasenia == $params["pass_old"] ){
			if( $params["pass_new_1"] == $params["pass_new_2"] ){
				$data = $this->updateEmpresa( 
					$empresa_id, 
					array(
						'preferencia_contrasenia' 	=> $params["pass_new_2"],
						'password' 					=> \Hash::make( $params["pass_new_2"] ),
						'api_token' 				=> \Hash::make( $params["pass_new_2"] )
					) 
				);
			} else {
				throw new \Exception("Contraseña Nueva no Coincide con su copia");
			}
		} else {
			throw new \Exception("Contraseña Anterior No Coincide");
		}
	}

	public function changeFacturaInfo( $empresa_id, $params ){
		if( isset( $params["fac_email"] ) && filter_var($params["fac_email"], FILTER_VALIDATE_EMAIL ) ){
			$this->updateEmpresa( 
				$empresa_id, 
				array(
					'fac_nombre' 		=> isset($params["fac_nombre"])    ? $params["fac_nombre"]    : "",
					'fac_apellido' 		=> isset($params["fac_apellido"])  ? $params["fac_apellido"]  : "",
					'fac_email' 		=> $params["fac_email"],
					'fac_telefono' 		=> isset($params["fac_telefono"])  ? $params["fac_telefono"]  : "",
					'fac_celular' 		=> isset($params["fac_celular"])   ? $params["fac_celular"]   : "",
					'fac_domicilio' 	=> isset($params["fac_domicilio"]) ? $params["fac_domicilio"] : ""
				)
			);
		} else {
			throw new \Exception("estructura de Email Invalido");
		}
	}

}
