<?php
namespace CVClient\Common\Repos;
//use CVClient\User;
//use CVClient\CV\Models\Oficina;
//use CVClient\CV\Models\Model;
use CVClient\CV\Repos\EmpresaRepo;
class MobileRepo{
    public function funcion( $funcion, $params ){
        return call_user_func_array( array( $this ,"func_".$funcion ), array( $params ) );
    }

    private function func_createempresa( $params ){
        return (new EmpresaRepo)->create( $params );
    }

    private function func_uniquevalidate( $params ){
        return (new EmpresaRepo)->register_uniqueValidate( $params );
    }
    private function func_sociallogin( $params ){
        $return = [ "message" => "", "load" => false, "existe" => 0 ];
        $socialcol = $params["social"] == "google_id"?"google_id":"facebook_id";
        $row = \DB::table("empresa")->where( $socialcol, $params["id"] )->first();
        if( !empty($row) ){
            $return = $this->func_login( [ "email" => $row->preferencia_login, "pass" => $row->preferencia_contrasenia ] );
        }else{
            $row = \DB::table("empresa")->where( "preferencia_login", $params["email"] )->first();
            if(!empty($row)){
                \DB::table("empresa")->where( "preferencia_login", $params["email"] )->update( 
                    array(
                        $socialcol => $params["id"]
                    )
                );
                $return = $this->func_login( [ "email" => $row->preferencia_login, "pass" => $row->preferencia_contrasenia ] );    
            }else{
                $return["message"] = "No se encuentra Registrado en Nuestro Sistema";    
            }
            
        }
        return $return;
    }
    private function func_login( $params ){
        $return = [ "message" => "", "estado" => "" , "data" => [], "load" => false, "existe" => 0 ];
        if( !isset($params["email"]) ){
            $return["message"] = "Email No Indicado";
        }
        if( !isset($params["pass"]) ){
            $return["message"] = "Password No Indicado";
        }
        if( isset($params["email"]) && isset($params["pass"]) ){
            $usu = \DB::table('empresa')->where( "preferencia_login", $params["email"] )->first();
            if( empty( $usu ) ){
                $return["message"] = "Email No Encontrado";
            }else{
                $return["existe"] = 1;
                $return["estado"] = $usu->preferencia_estado;
                if( $return["estado"] == 'A' || $return["estado"] == 'X' || $return["estado"] == 'S'){
                    if( $params["pass"] != $usu->preferencia_contrasenia ){
                        $return["message"] = "Contraseña No coincide";
                    }else{
                        $return["data"] = $usu;
                        $return["load"] = true;
                    }
                }else{
                    /*
                    if( $return["estado"] == 'S' ){
                        $return["message"] = "Suspendida por falta de pago";
                    }else */if( $return["estado"] == 'E' ){
                        $return["message"] = "Eliminado";
                    }else{
                        $return["message"] = "Creado, pero pendiente de actvar";
                    }
                }
            }
            
        }
        return $return;
    }
    private function func_asistencia( $params ){
        $r = [ "message" => "", "estado" => "" , "data" => [] ];
        $r["data"] = \DB::table("reserva_invitado")->where( "reserva_id", $params["reserva_id"] )->where( "dni", $params["dni"] )->update( array( "asistencia" => 1 ) );
        return $r;
    }
    private function func_invitado( $params ){
        $r = [ "message" => "", "estado" => "" , "data" => [] ];
        $vartouse = [];
        array_push( $vartouse, $params["reserva_id"] );
        array_push( $vartouse, $params["dni"] );
        array_push( $vartouse, isset($params["nomape"]) ? $params["nomape"] : "-" );
        array_push( $vartouse, isset($params["email"])  ? $params["email"]  : "-" );
        array_push( $vartouse, isset($params["movil"])  ? $params["movil"]  : "-" );
        $query = "INSERT INTO reserva_invitado( reserva_id, dni, nomape, email, movil, created_at, updated_at, asistencia, nuevo ) VALUES ( ?, ?, ?, ?, ?, NOW(), NOW(), 1, 1 ) ON DUPLICATE KEY UPDATE asistencia = 1";
        $r["data"] = (\DB::statement(\DB::raw($query), $vartouse));
        return $r;
    }


    private function func_list( $params ){
        $return = [ "message" => "", "estado" => "" , "data" => [] ];
        if( isset( $params["local"] ) ){
            $return["data"]["local"] = \DB::table('clocal')->where('estado','A')->get(['id','nombre','direccion','modeloids']);
        }
        if( isset( $params["modelo"] ) ){
            $return["data"]["modelo"] = \DB::table('modelo')->get(['id','nombre']);
        }
        if( isset( $params["oficina"] ) ){
            $return["data"]["oficina"] = \DB::table('oficina')->where('estado','A')->get(['id', 'nombre_o', 'local_id', 'modelo_id']);
        }
        if( isset( $params["eventos"] ) ){
            $vartouse = [];
            array_push( $vartouse, $params["local_id"] );
            array_push( $vartouse, $params["fecha"]    );
            $query = "
                SELECT 
                    a.*, e.empresa_nombre, o.piso_id, o.imagen, o.nombre AS 'oficina_nombre', o.capacidad, 
                    IFNULL( ( 
                        SELECT 
                            CONCAT( SUM( IF( ( asistencia>0 ), (1), (0) ) ), '-', COUNT(*) ) 
                        FROM 
                            reserva_invitado 
                        WHERE 
                            reserva_id = a.reserva_id 
                    ) , '0-0' ) AS 'invitado' 
                FROM (
                    SELECT 
                        id     AS 'reserva_id',    empresa_id,  oficina_id, 
                        nombre AS 'evento_nombre', hora_inicio, hora_fin
                    FROM reserva 
                    WHERE 
                        estado  IN ('A','J') AND 
                        oficina_id IN ( 
                            SELECT id FROM oficina WHERE modelo_id <> 1 AND local_id = ? 
                        ) AND 
                        fecha_reserva = ?
                ) a 
                LEFT JOIN empresa e ON e.id = a.empresa_id
                LEFT JOIN oficina o ON o.id = a.oficina_id
            ";
            $return["data"]["eventos"] = (\DB::select(\DB::raw($query), $vartouse));
        }
        if( isset( $params["invitado"] ) ){
            $vartouse = [];
            array_push( $vartouse, $params["reserva_id"] );
            array_push( $vartouse, $params["dni"]    );
            $query = "
                SELECT 
                    nomape, email, movil
                FROM 
                    reserva_invitado 
                WHERE 
                    reserva_id = ? AND 
                    dni        = ?
            ";
            $return["data"]["invitado"] = (\DB::select(\DB::raw($query), $vartouse));
        }
        if( isset( $params["concepto"] ) ){
            $return["data"]["concepto"] = \DB::table('concepto')->where('estado','A')->get();
        }
        return $return;
    }
}
?>