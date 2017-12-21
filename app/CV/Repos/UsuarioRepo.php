<?php
/**
 * Created by PhpStorm.
 * User: aquispe
 * Date: 2017/05/25
 * Time: 12:44
 **/

namespace CVClient\CV\Repos;


use CVClient\CV\Models\Rol;
use CVClient\User;
use Exception;
use Illuminate\Support\Facades\DB;
use PDOException;
use CVClient\Http\Controllers\Useful;


class UsuarioRepo
{
    use Useful;

    function _store($request)
    {
        try {
            $model = new User();
            $model->permisos = $this->crearPermisos((array)json_decode($request->datapermisos));
            $model->nombre = $request->nombre;
            $model->email = $request->email;
            $model->login = $request->login;
            $model->contrasenia = $request->contrasenia;
            $model->modulo_id = $request->modulo;
            $model->asesor = $request->asesor;
            $model->fecha_creacion = FECHA_HORA;
            $model->roles = $this->getRolesByDefault();
            $model->crm = '{"COBRO":"1","SERVICIO":"2","SUGERENCIA":"3","CENTRAL":"4","CONTRATO":"5","ENTREVISTA":"6"}';//valor por default
            if ($model->save()) {
                $this->returnSuccess($model);
            } else {
                throw new Exception();
            }
        } catch (PDOException $e) {
            $this->returnCatch($e);
        } catch (Exception $e) {
            $this->returnCatch($e);
        }
        return $this->rpta;
    }

    function _update($id, $request)
    {
        try {
            $model = User::findOrFail($id);
            $model->permisos = $this->crearPermisos((array)json_decode($request->datapermisos));
            $model->email = $request->email;
            $model->login = $request->login;
            $model->contrasenia = $request->contrasenia;
            $model->modulo_id = $request->modulo;
            $model->asesor = $request->asesor;
            if ($model->save()) {
                $this->returnSuccess($model);
            } else {
                throw new Exception();
            }
        } catch (PDOException $e) {
            $this->returnCatch($e);
        } catch (Exception $e) {
            $this->returnCatch($e);
        }
        return $this->rpta;
    }

    function _updateRoles($id, $request)
    {
        try {

            $arrayRoles = json_decode($request[0]);
            $reserva = [];
            $correspondencia = [];
            $recado = [];
            $mensajes = [];
            $empresas = [];
            $factura = [];
            $inicio = [];

            foreach ($arrayRoles as $key => $value) {

                if ($key == 'reserva') {
                    foreach ($value as $subkey => $subvalue) {
                        $reserva += [$subvalue->name => $subvalue->condicion];
                    }
                } elseif ($key == 'correspondencia') {
                    foreach ($value as $subkey => $subvalue) {
                        $correspondencia += [$subvalue->name => $subvalue->condicion];
                    }
                } elseif ($key == 'recado') {
                    foreach ($value as $subkey => $subvalue) {
                        $recado += [$subvalue->name => $subvalue->condicion];
                    }
                } elseif ($key == 'mensajes') {
                    foreach ($value as $subkey => $subvalue) {
                        $mensajes += [$subvalue->name => $subvalue->condicion];
                    }
                } elseif ($key == 'empresas') {
                    foreach ($value as $subkey => $subvalue) {
                        $empresas += [$subvalue->name => $subvalue->condicion];
                    }
                } elseif ($key == 'factura') {
                    foreach ($value as $subkey => $subvalue) {
                        $factura += [$subvalue->name => $subvalue->condicion];
                    }
                } elseif ($key == 'inicio') {
                    foreach ($value as $subkey => $subvalue) {
                        $inicio += [$subvalue->name => $subvalue->condicion];
                    }
                }

            }
            $arrayMerge= array_merge(['reserva' => $reserva], ['correspondencia' => $correspondencia], ['recado' => $recado], ['mensajes' => $mensajes], ['empresas' => $empresas], ['factura' => $factura], ['inicio' => $inicio]);
            $jsonRoles = json_encode($arrayMerge);


            $model = User::findOrFail($id);
            $model->roles = $jsonRoles;

            if ($model->save()) {
                $this->returnSuccess($model->roles);
            } else {
                throw new Exception();
            }

        } catch (PDOException $e) {
            $this->returnCatch($e);
        } catch (Exception $e) {
            $this->returnCatch($e);
        }

        return $this->rpta;
    }

    /**
     * Crea un array con los permisos escogidos
     * @param $accesos
     * @return String Un json con los permisos
     * @internal param $array $value La matriz superglobal $_POST
     * @internal param int $filas el numero de filas de datos de usuario para eliminar y quedarme con el array de solo permisos
     */
    function crearPermisos($accesos)
    {
        $modulosTop = array();
        $modulosLeft = array();

        foreach ($accesos as $key) {
            $data = explode('|', $key);
            if ($data[4] == 'TOP') {
                $modulosTop[] = array('modulo' => $data[0], 'pages' => $data[2], 'nombre' => $data[1], 'grupo' => $data[3], 'checked' => $data[5]);
            } else {
                $modulosLeft[$data[0]]['contenido'][$data[3]][] = $data[2];
                $modulosLeft[$data[0]]['contenido'][$data[3]][] = $data[1];
                $modulosLeft[$data[0]]['checked'] = $data[5];
            }
        }
        return json_encode(array('modulosTop' => $modulosTop, 'modulosLeft' => $modulosLeft));
    }

    /**
     * Retorna en una cadena JSON los roles por defecto
     * @return string
     */
    function getRolesByDefault()
    {
        $data = Rol::all();
        $result = [];
        foreach ($data as $key) {
            $rol = json_decode($key->action);
            $options = [];
            foreach ($rol as $var) {
                $options[$var->id] = '';
            }
            $result[$key->page] = $options;
        }
        return json_encode($result);
    }

    function _getListRoles()
    {
        try {
            $data = Rol::all();
            $this->returnSuccess($data);
        } catch (PDOException $e) {
            $this->returnCatch($e);
        }
        return $this->rpta;
    }

    function _getListPermisos()
    {
        try {
            $vartouse = [];
            $query = " SELECT modulo, pages,place, nombre, grupo FROM permisos WHERE estado = 'A' AND place = 'TOP'; ";
            $query2 = " SELECT modulo, pages,place, nombre, grupo FROM permisos WHERE estado = 'A' AND place = 'LEFT'; ";

            $data = DB::select(DB::raw($query), $vartouse);
            $data2 = DB::select(DB::raw($query2), $vartouse);

            $this->returnSuccess(array_merge($data, $data2));

        } catch (PDOException $e) {
            $this->returnCatch($e);
        }
        return $this->rpta;
    }

    function _getListModulos()
    {
        try {
            $vartouse = [];
            $query = " SELECT id, modulo FROM modulo where estado = 'A' ";

            $data = DB::select(DB::raw($query), $vartouse);

            $this->returnSuccess($data);

        } catch (PDOException $e) {
            $this->returnCatch($e);
        }
        return $this->rpta;
    }

    function _listAll($getparams)
    {
        try {
            $vartouse = [];

            $sql = " select * from usuario where 1 = 1 ";
            if (isset($getparams['estado']) && $getparams['estado'] != '-') {
                $sql .= " AND estado = ? ";
                array_push($vartouse, $getparams['estado']);
            }

            $query = "SELECT SQL_CALC_FOUND_ROWS * FROM (" . $sql . ") x ORDER BY x.fecha_creacion DESC ";

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

    function _changeEstado($id,$request)
    {
        try {

            $model = User::findOrFail($id);
            $model->estado = $request->estado;

            if ($model->save()) {
                $this->returnSuccess($model->estado);
            } else {
                throw new Exception();
            }

        } catch (PDOException $e) {
            $this->returnCatch($e);
        } catch (Exception $e) {
            $this->returnCatch($e);
        }

        return $this->rpta;

    }

}