<?php namespace CVClient\Http\Controllers\CV;

use Auth;
use DB;
use CVClient\Http\Controllers\Controller;
use CVClient\CV\Repos\EmpresaRepo;
use CVClient\CV\Repos\EmpleadoRepo;
use CVClient\Common\Repos\SessionRepo;

class empleadoController extends Controller {

	private $repo = null;

	public function __construct(){
		$this->repo = new EmpleadoRepo;
	}

	public function create($id){
		$params = request()->only(['nombre','apellido','correo','dni']);
		$params['empresa_id'] = $id;
		try {
			DB::beginTransaction();
			// Obtener empresa
			$empresa = (new EmpresaRepo)->getById($id);

			// Crear empleado
			$this->repo->create($params);

			// Enviar notificacion con Pusher
            ( new SessionRepo )->Notification(['empresa_id'=>$id,'empresa_nombre'=>$empresa->empresa_nombre] ,'agregó un nuevo empleado ( ' . $params['nombre'] . ' ) para', Auth::user()->nombre, 'Empresa empleado');
            DB::commit();
            // Retornar
            return response()->json(['message'=>'Empleado creado.', 'employees'=>$empresa->empleados]);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}

	public function delete($id, $empleado_id){
		try {
			DB::beginTransaction();
			// Obtener empresa
			$empresa = (new EmpresaRepo)->getById($id);

			// Crear empleado
			$this->repo->delete($id,$empleado_id);

			// Enviar notificacion con Pusher
            ( new SessionRepo )->Notification(['empresa_id'=>$id,'empresa_nombre'=>$empresa->empresa_nombre] ,'eliminó un empleado de', Auth::user()->nombre, 'Empresa empleado');
            DB::commit();
            // Retornar
            return response()->json(['message'=>'Empleado eliminado.', 'employees'=>$empresa->empleados]);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}
	public function update($id, $empleado_id){
		$params = request()->only(['nombre','apellido','correo','dni']);
		$params['empresa_id'] = $id;
		$params['id'] = $empleado_id;
		try {
			DB::beginTransaction();
			// Obtener empresa
			$empresa = (new EmpresaRepo)->getById($id);

			// Crear empleado
			$this->repo->update($params);

			// Enviar notificacion con Pusher
            ( new SessionRepo )->Notification(['empresa_id'=>$id,'empresa_nombre'=>$empresa->empresa_nombre] ,'editó el empleado ( ' . $params['nombre'] . ' ) de', Auth::user()->nombre, 'Empresa empleado');
            DB::commit();
            // Retornar
            return response()->json(['message'=>'Datos del Empleado actualizado.', 'employees'=>$empresa->empleados]);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}

}