<?php
/**
 * Created by PhpStorm.
 * User: QuispeRoque
 * Date: 21/04/17
 * Time: 12:50
 */

namespace CVClient\CV\Repos;


use Carbon\Carbon;
use CVClient\CV\Models\Conversacion;
use CVClient\CV\Models\Mensaje;
use CVClient\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDOException;

class MensajeRepo extends Controller
{
    function getConversationsList($getparams)
    {
        try {
            $vartouse = [];
            $sql = " SELECT e.id AS empresa_id, m.id, e.empresa_nombre, asunto, mensaje, created_at, respondido, leido, IF(usuario = 'Cliente', 1, 2) AS tipo, usuario,
                     (SELECT COUNT(id) FROM conversacion WHERE mensaje_id = m.id AND leido = 'N') AS no_leidos, YEAR(created_at) AS anio, MONTH(created_at) AS mes, m.estado, movil
                     FROM mensaje AS m
                     JOIN empresa AS e ON m.vto = e.id
                     WHERE vfrom != 1 ";

            if (isset($getparams['estado']) && $getparams['estado'] != '-') {
                $sql .= " AND estado = ? ";
                array_push($vartouse, $getparams['estado']);
            }
            if (isset($getparams['anio']) && $getparams['anio'] != '-') {
                $sql .= " AND YEAR(created_at) = ? ";
                array_push($vartouse, $getparams['anio']);
            }
            if (isset($getparams['mes']) && $getparams['mes'] != '-') {
                $sql .= " AND MONTH(created_at) = ? ";
                array_push($vartouse, $getparams['mes']);
            }
            if (isset($getparams['tipo']) && $getparams['tipo'] == 1) {
                $sql .= " AND usuario  = 'Cliente' ";
            } else {
                $sql .= " AND usuario  != 'Cliente' ";
            }
            if (isset($getparams['empresa_id']) && $getparams['empresa_id'] > 0) {
                $sql .= " AND vto = ? ";
                array_push($vartouse, $getparams['empresa_id']);
            }

            $query = "SELECT SQL_CALC_FOUND_ROWS * FROM (" . $sql . ") x ORDER BY x.created_at DESC ";

            if (isset($getparams["limite"])) {
                if (isset($getparams["pagina"]) && $getparams["pagina"] > 0) {
                    $query .= " LIMIT " . (($getparams["pagina"] - 1) * $getparams["limite"]) . "," . $getparams["limite"];
                } else {
                    $query .= " LIMIT " . $getparams["limite"];
                }
            }

            $rows = DB::select(DB::raw($query), $vartouse);
            $tota = DB::select(DB::raw("SELECT FOUND_ROWS() AS 'rows'"));

            $this->rpta = ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];

        } catch (PDOException $e) {
            $this->returnCatch($e);
        }
        return $this->rpta;
    }

    function conversations($getparams)
    {
        try {
            $vartouse = [];
            $query = " SELECT id, mensaje_id, respuesta, empleado, leido, leido_user, created_at, movil FROM conversacion WHERE mensaje_id = ? ";
            array_push($vartouse, $getparams->mensaje_id);

            $rows = DB::select(DB::raw($query), $vartouse);

            $this->rpta = ['load' => true, "rows" => $rows];

        } catch (PDOException $e) {
            $this->returnCatch($e);
        }
        return $this->rpta;
    }

    function nuevaRespuesta($request)
    {
        try {
            $model = new Conversacion();
            $model->mensaje_id = $request->mensaje_id;
            $model->respuesta = $request->respuesta;
            $model->empleado = Auth::user()->nombre;
            $model->created_at = Carbon::now()->format('Y-m-d H:i:s');
            $model->leido = 'S';
            if ($model->save()) {
                $this->rpta = ['load' => true, 'data' => $model,'conversacion_id'=>$model->getKey(), 'msg' => 'respuesta enviada'];
            }
        } catch (PDOException $e) {
            $this->returnCatch($e);
        }
        return $this->rpta;
    }

}