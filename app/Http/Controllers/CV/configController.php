<?php namespace CVClient\Http\Controllers\CV;
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Origin: *');

use CVClient\Http\Controllers\Controller;
use CVClient\CV\Models\Local;
use CVClient\CV\Models\Oficina;

class configController extends Controller {


	public function getLocalList(){
		try {
			return response()->json(Local::orderBy('nombre','ASC')->get(), 200);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}

	public function getOficinaList(){
		try {

			$params = request()->all();

			$r = Oficina::from('oficina as o')
					->join('modelo as m', 'm.id', '=', 'o.modelo_id')
					->join('clocal as c','c.id','=','o.local_id');

			if(isset($params['estado']) && !empty($params['estado'])){
				$r->where('o.estado', $params['estado']);
			}

			if(isset($params['local_id']) && !empty($params['local_id'])){
				$r->where('o.local_id', $params['local_id']);
			}

			if(isset($params['modelo_id']) && !empty($params['modelo_id'])){
				$r->where('o.modelo_id', $params['modelo_id']);
			}

			$r = $r->get(['o.id','o.modelo_id','o.local_id','o.capacidad','o.nombre_o','o.estado','c.nombre as local_nombre','m.nombre as modelo_nombre']);

			return response()->json($r, 200);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()], 412);
		}
	}

	public function createLocal(){
		try {
			$params = request()->only(['nombre','direccion','distrito','estado']);
			Local::create($params);
			return response()->json(['message'=>'Nuevo local agregado']);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()]);
		}
	}

	public function createOficina(){
		try {
			$params = request()->only(['nombre_o','local_id','modelo_id','capacidad']);
			$params['estado'] = 'A';
			Oficina::create($params);
			return response()->json(['message'=>'Nueva oficina agregado']);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()]);
		}
	}

	public function updateLocal($local_id){
		try {
			$params = request()->only(['nombre','direccion','distrito','estado']);
			$local = Local::findOrFail((int)$local_id);
			$local->fill($params);
			$local->save();
			return response()->json(['message'=>'Datos del local actualizado']);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()]);
		}
	}

	public function updateOficina($oficina_id){
		try {
			$params = request()->only(['nombre_o','local_id','modelo_id','capacidad']);
			$office = Oficina::findOrFail((int)$oficina_id);
			$office->fill($params);
			$office->save();
			return response()->json(['message'=>'Datos del oficina actualizado']);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()]);
		}
	}

	public function updateStatusOficina($oficina_id){
		try {
			$params = request()->only(['estado']);

			$params['estado'] = ($params['estado']=='A')?'I':'A';
			$office = Oficina::findOrFail((int)$oficina_id);
			$office->fill($params);
			$office->save();
			return response()->json(['message'=>'Oficina Actualizado.']);
		} catch (\Exception $e) {
			return response()->json(['message'=>$e->getMessage()]);
		}
	}
}