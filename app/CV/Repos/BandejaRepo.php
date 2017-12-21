<?php
namespace CVClient\CV\Repos;
use CVClient\CV\Models\Bandeja;
use CVClient\CV\Models\BandejaMensaje;
use CVClient\CV\Repos\ServicioRepo;
use CVClient\Common\Repos\QueryRepo;
class BandejaRepo{

	use \CVClient\Traits\NotificationTrait;

	public function getMyMessages( $tipo, $id, $params )
	{
		$params['de_tipo'] 	= $tipo;
		$params['de'] 		= $id;
		$params['a_tipo'] 	= $tipo;
		$params['a'] 		= $id;
		return $data = ( new QueryRepo )->Q_bandeja( $params );
	}

	public function getMyReceivedMessages( $tipo, $id, $params )
	{
		$params['a_tipo'] 	= $tipo;
		if( $id > 0 ){
			$params['a'] 	= $id;
		}
		return $data = ( new QueryRepo )->Q_bandeja( $params );
	}

	public function getMySendMessages( $tipo, $id, $params )
	{
		$params['de_tipo'] 	= $tipo;
		$params['de'] 		= $id;
		return $data = ( new QueryRepo )->Q_bandeja( $params );
	}

	public function getMessageDetail( $message_id )
	{
		$params = [ "padre_id" => $message_id ];//"respuesta_id" => $message_id, 
		return $data = ( new QueryRepo )->Q_bandeja( $params );
	}

	public function postNewMessages( $params )
	{
		$bandeja = Bandeja::create(
			array(
				'de_tipo'		=> "C",
				'de'			=> \Auth::user()->id,
				'a_tipo'		=> "E",
				'a'				=> $params["a"],
				'empresa_id'	=> \Auth::user()->id,
				'asunto'		=> $params["asunto"],
				'leido'			=> 0,
				'padre_id'		=> isset( $params["padre_id"] ) 	? $params["padre_id"]     : 0,
				'respuesta_id'	=> isset( $params["respuesta_id"] ) ? $params["respuesta_id"] : '0',
				'created_at'	=> date("Y-m-d H:i:s"),
				'updated_at'	=> date("Y-m-d H:i:s")
			)
		);
		
		$bandejamensaje = BandejaMensaje::create( array( "id" => $bandeja["id"], "mensaje" => $params["mensaje"] ) );

		try {
			$empresa = \Auth::user();

			$message = ($params["asunto"]=="H")?"Solicitud de Horas":$params["mensaje"];

			$this->sendWSMessage("E", [
				"from" => $empresa->empresa_nombre,
				"empresa_id" => $empresa->id,
				"message" => $message,
				"module" => "bandeja",
				"created_at" => date('d/m/y H:i')
			]);
		} catch (\Exception $e){
			\Log::error($e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine() );
			\Log::error($e->getTraceAsString());
		}

		return $bandeja;
	}

	public function putReadMessages( $message_id )
	{
		return $bandeja = Bandeja::where( "id", $message_id )->update( 
			array( 
				"leido" 	=> 1,
				"quienleyo" => \Auth::user()->empresa_nombre
			) 
		);
	}
}
?>