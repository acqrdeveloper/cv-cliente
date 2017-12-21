<?php
/**
 * @author Kevin W. Baylon Huerta <kbaylonh@hotmail.com>
 */

namespace CVClient\CV\Repos;

use Auth;
use Carbon\Carbon;
use DB;
use CVClient\CV\Models\Central;
use CVClient\CV\Models\CentralOpcion;
use CVClient\CV\Models\Empresa;
use CVClient\CV\Repos\EmpresaRepo;
use CVClient\Common\Repos\SessionRepo;

class CentralRepo{

	/**
	 * Crea una nueva central.
	 * @param array $params Datos para crear la central.
	 * @return \CVClient\CV\Models\Central Instancia del modelo Central.
	 */
	private function create($params){
		// create central
		$central = new Central;
		$central->numero = $params['numero'];
		$central->cancion = $params['cancion'];
		$central->texto = $params['texto'];
		$central->save();

		// update empresa
		$empresa = (new EmpresaRepo)->getById($params['empresa_id']);
		$empresa->central_id = $central->id;
		$empresa->central = 'S';
		$empresa->save();

		return $central;
	}

	/**
	 * Borra una opcion de la central.
	 * @param array $params Datos de la opcion
	 * @return void
	 */
	public function deleteOption($params){
		DB::beginTransaction();
		$opcion = CentralOpcion::where('central_id', $params['central_id'])->where('id', $params['id'])->first();
		$opc_num = $opcion->opcion_numero;
		$ane_num = $opcion->anexo_numero;
		$opcion->delete();
		$empresa= Empresa::where('central_id', $params['central_id'])->first();
		(new SessionRepo)->Notification(['empresa_id'=>$empresa->id, 'empresa_nombre'=>$empresa->empresa_nombre], 'eliminó la opción ' . $opc_num . ' con anexo ' . $ane_num . ' de', Auth::user()->nombre, 'Central');
		DB::commit();
	}

	/**
	 * Obtiene la informacion de la central usando el ID de empresa vinculado a este.
	 * @param int $empresa_id ID de la empresa.
	 * @return \CVClient\CV\Models\Central instancia del modelo Central.
	 */
	public function getByCompanyId($empresa_id){
		$empresa = Empresa::find($empresa_id);

		if(is_null($empresa)){
			throw new \Exception("La empresa no existe");
		}

		$central = $empresa->pbx;

		if(is_null($central) || $central->id == 440){
			return ['have'=>'N', 'cdr'=>$empresa->preferencia_cdr];
		}

		return [
			'cancion' => $central->cancion,
			'cdr' => $empresa->preferencia_cdr,
			'have' => $empresa->central,
			'id' => $central->id,
			'numero' => $central->numero,
			'opciones' => $central->opciones,
			'texto' => $central->texto
		];
	}

	/**
	 * Obtiene la informacion de una central usando su ID.
	 * @param id $id 
	 * @return \CVClient\CV\Models\Central instancia del modelo Central.
	 * @throws \Exception Dispara una excepcion cuando la central buscada no existe.
	 */
	public function getById($id){
		$central = Central::find($id);

		if(is_null($central))
			throw new \Exception("La empresa no tiene central.");

		return $central;
	}

	public function getCentral($id){

		$central = $this->getById($id);

		return [
			'numero' => $central->numero,
			'cancion' => $central->cancion,
			'texto' => $central->texto,
			'opciones' => $central->opciones
		];
	}

	/**
	 * Crea una nueva opcion en una central.
	 * @param array $params Datos de la nueva opcion.
	 * @return (int|\CVClient\CV\Models\Empresa)[] retorna en un arreglo el id de la nueva opcion y la instancia de la empresa.
	 * @throws \Exception Dispara error si el numero de la nueva opcion ya existe.
	 */
	public function newOption($params){
		// Se inicia una transaccion en la BD
		DB::beginTransaction();

		// Se busca la central
		$central = $this->getById($params['id']);

		// Buscar opcion
		$existe = $central->opciones()->where('central_id', $params['central_id'])->where('opcion_numero', $params['opcion_numero'])->first();

		if(!is_null($existe)){
			throw new \Exception("El número de opción ya está ingresado.");
		}

		// Se crea la opcion
		$opcion = $central->opciones()->create([
			'opcion_numero' => $params['opcion_numero'],
			'opcion_nombre' => $params['opcion_nombre'],
			'anexo_numero'  => $params['anexo_numero'],
			'anexo_nombre'  => $params['anexo_nombre'],
			'redireccion'   => $params['redireccion']
		]);

		// Creamos un nuevo empleado
		$empresa = $central->empresa;

		if(isset($params['empleado']) && $params['empleado'] == 'on'){
			$empresa->empleados()->create([
				'nombre' => $params['anexo_nombre'],
				'opcion_central_id' => $opcion->id
			]);
		}

		// Se manda un pusher
		(new SessionRepo)->Notification(['empresa_id'=>$empresa->id, 'empresa_nombre'=>$empresa->empresa_nombre], 'agregó opción ' . $params['opcion_numero'] . ' con anexo ' . $params['anexo_numero'] . ' para', Auth::user()->nombre, 'Central');

		// Se guardan los cambios en la BD
		DB::commit();
	}

	/**
	 * Actualiza la informacion de la central
	 * @param array $params Datos nuevos de la central 
	 * @return CVClient\CV\Models\Central Instancia del modelo central
	 */
	private function update($params){
		// Se actualiza la info de la central
		$central = Central::find($params['id']);
		$central->numero = $params['numero'];
		$central->cancion = $params['cancion'];
		$central->texto = $params['texto'];
		$central->save();

		// Se crea un historial en empresa
		$central->empresa->historial()->create([
			'estado' => 'N',
			'observacion' => 'CENTRAL EDITADO: ' . $params['numero'] . ' -|- ' . $params['cancion'] . ' -|- ' . $params['texto'],
			'empleado' => Auth::user()->nombre,
			'fecha' => Carbon::now()->format('Y-m-d H:i:s')
		]);

		// Se retorna la instancia de la central
		return $central;
	}

	/**
	 * Actualiza la configuracion de una central
	 * @param array $params datos nuevos de la central
	 * @return (int|boolean)[] Devuelve el id de la central y si fue editado o no
	 */
	public function updateConfig($params){
		DB::beginTransaction();
		// Si existe el id de la central, se modificara
		if(isset($params['id']) && $params['id'] > 0){
			$central = $this->update($params);
		// Caso contrario, se creara la central
		} else {
			$central = $this->create($params);
		}
		DB::commit();
		return ['id'=>$central->id, 'edit' => (isset($params['id']) && $params['id'] > 0)];
	}

	/**
	 * Actualiza la informacion de una opcion de una central
	 * @param array $params datos de la opcion
	 * @return void
	 */
	public function updateOption($params){
		// Iniciar transaccion
		DB::beginTransaction();

		// obtenemos la central
		$central = $this->getById($params['central_id']);

		// Obtenemos la opcion
		CentralOpcion::where('id', $params['id'])->update([
			'opcion_numero' => $params['opcion_numero'],
			'opcion_nombre' => $params['opcion_nombre'],
			'anexo_numero' => $params['anexo_numero'],
			'anexo_nombre' => $params['anexo_nombre'],
			'redireccion' => $params['redireccion']
		]);

		// Creamos un historial en empresa
		$empresa = $central->empresa;
		$empresa->historial()->create([
			'estado' => 'N',
			'observacion' => 'CENTRAL OPCIÓN (EDITADO): '.$params['opcion_numero'].' -|- '.$params['opcion_nombre'].' -|- '.$params['anexo_numero'].' -|- '.$params['anexo_nombre'].' -|- '. $params['redireccion'],
			'empleado' => Auth::user()->nombre,
			'fecha' => Carbon::now()->format('Y-m-d H:i:s')
		]);

		// Guardar cambios en DB
		(new SessionRepo)->Notification(['empresa_id'=>$empresa->id, 'empresa_nombre'=>$empresa->empresa_nombre], 'editó la opción ' . $params['opcion_numero'] . ' con anexo ' . $params['anexo_numero'] . ' de', Auth::user()->nombre, 'Central');
		DB::commit();
	}
}
?>