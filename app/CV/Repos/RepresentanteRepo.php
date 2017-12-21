<?php namespace CVClient\CV\Repos;

use Carbon\Carbon;
use DB;
use CVClient\CV\Models\Representante;
use CVClient\CV\Models\Empresa;

class RepresentanteRepo{

	public function create($params){
		
		$agente = new Representante();
		$agente->nombre = $params['nombre'];
		$agente->apellido = $params['apellido'];
		$agente->dni = $params['dni'];
		$agente->correo = $params['correo'];
		$agente->empresa_id = $params['empresa_id'];
		$agente->telefonos = $params['telefonos'];
		$agente->domicilio = $params['domicilio'];
		$agente->is_login = 'N';
		$agente->fecha = Carbon::now()->format('Y-m-d H:i:s');
		$agente->save();
	}

	public function delete($empresa_id, $repre_id){
		
		$agente = Representante::find($repre_id);

		if(is_null($agente))
			throw new \Exception("El representante buscado no existe");

		if($agente->empresa_id != $empresa_id)
			throw new \Exception("El representante no pertenece a esta empresa");

		$nombre = $agente->nombre;
		$agente->delete();
		return $nombre;
	}

	public function update($params){
		
		$agente = Representante::find($params['id']);

		if(is_null($agente))
			throw new \Exception("El representante buscado no existe");

		if($agente->empresa_id != $params['empresa_id'])
			throw new \Exception("El representante no pertenece a esta empresa");

		$agente->nombre = $params['nombre'];
		$agente->apellido = $params['apellido'];
		$agente->dni = $params['dni'];
		$agente->correo = $params['correo'];
		$agente->telefonos = $params['telefonos'];
		$agente->domicilio = $params['domicilio'];

		$agente->save();
	}

	public function updateLogin($params){
		Representante::where('empresa_id', $params['empresa_id'])->update(['is_login'=>'N']);

		$agente = Representante::find($params['id']);

		if(is_null($agente))
			throw new \Exception("El representante buscado no existe");

		if($agente->empresa_id != $params['empresa_id'])
			throw new \Exception("El representante no pertenece a esta empresa");

		$agente->is_login = 'S';
		$agente->save();

		return $agente->correo;
	}
}