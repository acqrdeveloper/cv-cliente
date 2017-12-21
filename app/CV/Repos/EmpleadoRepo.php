<?php
namespace CVClient\CV\Repos;
use DB;
use CVClient\CV\Models\Empleado;
use CVClient\CV\Models\Empresa;

class EmpleadoRepo{

	public function create($params){
		
		$empleado = new Empleado();
		$empleado->nombre = $params['nombre'];
		$empleado->apellido = $params['apellido'];
		$empleado->dni = $params['dni'];
		$empleado->correo = $params['correo'];
		$empleado->empresa_id = $params['empresa_id'];
		$empleado->save();
	}

	public function delete($empresa_id, $empleado_id){
		
		$empleado = Empleado::find($empleado_id);

		if(is_null($empleado))
			throw new \Exception("El empleado buscado no existe");

		if($empleado->empresa_id != $empresa_id)
			throw new \Exception("El empleado no pertenece a esta empresa");

		$empleado->delete();
	}

	public function update($params){
		
		$empleado = Empleado::find($params['id']);

		if(is_null($empleado))
			throw new \Exception("El empleado buscado no existe");

		if($empleado->empresa_id != $params['empresa_id'])
			throw new \Exception("El empleado no pertenece a esta empresa");

		$empleado->nombre = $params['nombre'];
		$empleado->apellido = $params['apellido'];
		$empleado->dni = $params['dni'];
		$empleado->correo = $params['correo'];

		$empleado->save();
	}
}