<?php
namespace CVClient\Common\Repos;
use DB;
use PHPExcel;
use PHPExcel_IOFactory;
use CVClient\Common\Repos\SessionRepo;
class QueryRepo
{

    public function Q_horario_local()
    {
        return ( new SessionRepo )->CallRaw("mysql", "AL_HORARIO_LOCAL", []);
    }

    public function Q_cliente_factura_item( $params )
    {
        $vartouse = [];
        $query_level0 = "SELECT SQL_CALC_FOUND_ROWS id, factura_id, descripcion, precio, anio, mes, tipo, custom_id FROM factura_item WHERE estado = 'A' AND factura_id = ?";
        array_push($vartouse, $params["factura_id"]);
        $rows = (\DB::select(\DB::raw($query_level0), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'")));
        return ["rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_cliente_reporte_cdr( $params )
    {
    	$sql = "SELECT SQL_CALC_FOUND_ROWS calldate, IF(src = 'Unknown', 'Desconocido', src) AS 'src', IF(substring(dst from 1 for 3) = '511',substring(dst from 4),dst) as 'dst',SEC_TO_TIME(billsec) AS 'tiempo', disposition FROM astcdr WHERE userfield = '" . \Auth::user()->id . "' AND dst != 's' AND dst NOT LIKE '9%'";


    	/**
        $vartouse = [];
        
        $queyry01 = "SELECT central_id, LOWER(cdr) as 'cdr' FROM empresa_central WHERE empresa_id = ? AND estado = 'A'";
        array_push( $vartouse, \Auth::user()->id );

        $queyry11 = "SELECT a.*, c.numero FROM (".$queyry01.")  a LEFT JOIN central c ON c.id = a.central_id";

        $queyry02 = "SELECT LOWER(userfield) AS 'cdr', calldate, IF(src = 'Unknown', 'Desconocido', src) AS 'src', dst,SEC_TO_TIME(billsec) AS 'tiempo', disposition FROM cdr WHERE dst != 's' AND LENGTH(dst) <= 3";
        //AND DATE(calldate) BETWEEN :inicio AND :fin;
        if ($params["anio"] != "-" && $params["anio"] > 0) {
            $queyry02 .= " AND YEAR(calldate) = ?";
            array_push($vartouse, $params["anio"]);
        }
        if ($params["mes"] != "-" && $params["mes"] > 0) {
            $queyry02 .= " AND MONTH(calldate) = ?";
            array_push($vartouse, $params["mes"]);
        }

        if (isset($params["estado"])) {
            $queyry02 .= " AND disposition = ?";
            array_push($vartouse, $params["estado"]);
        }

        $query_level0 = "SELECT a.central_id, a.numero, c.* FROM (".$queyry11.") a LEFT JOIN (".$queyry02.") c ON c.cdr = a.cdr WHERE c.cdr IS NOT NULL ORDER BY c.calldate DESC";
        $query_level1 = "SELECT SQL_CALC_FOUND_ROWS a.* FROM ( " . $query_level0 . " ) a ";
		*/
        if ($params["anio"] != "-" && $params["anio"] > 0) {
            $sql .= " AND YEAR(calldate) = " . $params["anio"];
        }
        if ($params["mes"] != "-" && $params["mes"] > 0) {
            $sql .= " AND MONTH(calldate) = " . $params["mes"];
        }

        if (isset($params["estado"]) && $params["estado"] != "-") {
            $sql .= " AND disposition = '" . $params["estado"] . "'";
        }

        if (isset($params["dst"]) && $params["dst"] != "") {
            $sql .= " AND dst = '" . $params["dst"] . "'";
        }

        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $sql .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $sql .= " LIMIT " . $params["limite"];
            }
        }
        

        $rows = \DB::connection('pbx')->select(\DB::raw($sql));
        $tota = \DB::connection('pbx')->select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        $numbers = \DB::connection('pbx')->select("SELECT number FROM number WHERE customer_id = ?", [\Auth::user()->id]);
        return ["rows" => $rows, "total" => $tota[0]->rows, "numbers"=>$numbers];
    }

    public function Q_garantia_disponible( $params )
    {
        $vartouse = [];
        $query_level0 = "SELECT * FROM factura_item WHERE factura_id IN (
            SELECT id FROM factura WHERE empresa_id = ?
        ) AND tipo = 'G' AND custom_id > 0 ORDER BY id LIMIT 1";
        array_push( $vartouse, $params['empresa_id'] );
        $query_level1 = "SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level0 . ") AS r ";
        $rows = \DB::select(\DB::raw($query_level1), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_facturacion_mensual_empresas( $params )
    {
        $vartouse = [];
        $query_level0 = "SELECT id, preferencia_comprobante AS 'comprobante', empresa_nombre, empresa_ruc,
            fac_nombre, fac_apellido, fac_dni, fac_email, fecha_creacion, updated_at, plan_id FROM empresa WHERE id  NOT IN ( SELECT empresa_id FROM factura WHERE id IN ( SELECT factura_id FROM factura_item WHERE anio = ?  AND mes =  ? AND tipo = 'P' ) OR estado = 'PENDIENTE' ) AND preferencia_estado = 'A' AND preferencia_facturacion = ? AND DATE( IFNULL( fecha_creacion, updated_at ) ) <= DATE( LAST_DAY( CONCAT( ? ,'-', ?,'-1' ) ) ) AND preferencia_comprobante <> '' AND ( convenio = '' OR convenio = 'N' )";
        array_push( $vartouse, $params['anio'] );
        array_push( $vartouse, $params['mes']  );
        array_push( $vartouse, $params['ciclo']  );
        array_push( $vartouse, $params['anio'] );
        array_push( $vartouse, $params['mes']  );

        $query_level1 = "SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level0 . ") AS r ";
        $rows = \DB::select(\DB::raw($query_level1), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_recurso_periodo($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT empresa_id, anio, mes, cantidad_copias, cantidad_impresiones, horas_reunion, horas_privada, horas_capacitacion FROM recurso_periodo WHERE empresa_id > 0";

        if ( isset($params['empresa_id']) && $params['empresa_id'] != '-' && $params['empresa_id'] > 0 ) {
            $query_level0 .= " AND empresa_id = ? ";
            array_push( $vartouse, $params['empresa_id'] );
        }

        if(isset($params['anio']) && $params['anio'] > 0 ){
            $query_level0 .= " AND anio = ?";
            array_push( $vartouse, $params['anio'] );
        }

        if(isset($params['mes']) && $params['mes'] > 0 ){
            $query_level0 .= " AND mes = ?";
            array_push( $vartouse, $params['mes'] );
        }

        $query_level1 = "SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level0 . ") AS r ";
        $query_level1 .= " ORDER BY r.anio DESC, r.mes DESC";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level1 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level1 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = \DB::select(\DB::raw($query_level1), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_bandeja($params)
    {
        $vartouse = [];//'' AS 'mensaje',
        $query_level0 = "SELECT id AS 'bandeja_id', empresa_id, de_tipo, de, a_tipo, a, asunto,  leido, padre_id, respuesta_id, created_at, updated_at FROM bandeja WHERE id IS NOT NULL";

        if ( isset($params['empresa_id']) && $params['empresa_id'] != '-' && $params['empresa_id'] > 0 ) {
            $query_level0 .= " AND empresa_id = ? ";
            array_push($vartouse, $params['empresa_id']);
        }

        if ( isset($params['asunto']) && $params['asunto'] != '-' ) {
            $query_level0 .= " AND asunto = ? ";
            array_push($vartouse, $params['asunto']);
        }

        if(isset($params['anio']) && $params['anio'] > 0 ){
            $query_level0 .= " AND YEAR( created_at ) = ?";
            array_push($vartouse, $params['anio']);
        }

        if(isset($params['mes']) && $params['mes'] > 0 ){
            $query_level0 .= " AND MONTH( created_at ) = ?";
            array_push($vartouse, $params['mes']);
        }

        if( isset($params['respuesta_id']) && $params['respuesta_id'] != '-' && $params['respuesta_id'] > 0 && isset($params['padre_id']) && $params['padre_id'] != '-' && $params['padre_id'] > 0 ){
                $query_level0 .= " AND ( respuesta_id = ? OR padre_id = ? OR id = ? ) ";
                array_push($vartouse, $params['respuesta_id']);
                array_push($vartouse, $params['padre_id']);
                array_push($vartouse, $params['padre_id']);
        }else{
            if ( isset($params['respuesta_id']) && $params['respuesta_id'] != '-' && $params['respuesta_id'] > 0 ) {
                $query_level0 .= " AND respuesta_id = ? ";
                array_push($vartouse, $params['respuesta_id']);
            }

            if ( isset($params['padre_id']) && $params['padre_id'] != '-' && $params['padre_id'] > 0 ) {
                $query_level0 .= " AND ( padre_id = ? OR id = ? )";
                array_push($vartouse, $params['padre_id']);
                array_push($vartouse, $params['padre_id']);
            }
        }

        if ( isset($params['leido']) && $params['leido'] != '-' ) {
            $query_level0 .= " AND leido = ? ";
            array_push($vartouse, $params['leido']);
        }

        if ( isset($params['de']) && $params['de'] != '-' && $params['de'] > 0 && isset($params['a']) && $params['a'] != '-' && $params['a'] > 0 ) {
                $query_level0 .= " AND ( ( de_tipo = ? AND de = ? ) OR ( a_tipo = ? AND a = ?  ) ) ";
                array_push($vartouse, $params['de_tipo']);
                array_push($vartouse, $params['de']);
                array_push($vartouse, $params['a_tipo']);
                array_push($vartouse, $params['a']);

        }else{
            if ( isset($params['de_tipo']) && $params['de_tipo'] != '-' ) {
                $query_level0 .= " AND de_tipo = ? ";
                array_push($vartouse, $params['de_tipo']);
            }

            if ( isset($params['de']) && $params['de'] != '-' && $params['de'] > 0 ) {
                $query_level0 .= " AND de = ? ";
                array_push($vartouse, $params['de']);
            }

            if ( isset($params['a_tipo']) && $params['a_tipo'] != '-' ) {
                $query_level0 .= " AND a_tipo = ? ";
                array_push($vartouse, $params['a_tipo']);
            }

            if ( isset($params['a']) && $params['a'] != '-' && $params['a'] > 0 ) {
                $query_level0 .= " AND a = ? ";
                array_push($vartouse, $params['a']);
            }
        }

        $query_level1 = "SELECT b.*, e.preferencia_estado, e.preferencia_login, e.preferencia_facturacion, e.empresa_nombre, e.empresa_ruc, e.fac_nombre, e.fac_apellido, e.fac_email FROM ( ".$query_level0." ) b LEFT JOIN empresa e ON e.id = b.empresa_id WHERE e.id IS NOT NULL";

        if ( isset($params['preferencia_estado']) && $params['preferencia_estado'] != '-' ) {
            $query_level1 .= " AND e.preferencia_estado = ? ";
            array_push($vartouse, $params['preferencia_estado']);
        }

        if ( isset($params['ciclo']) && $params['ciclo'] != '-' ) {
            $query_level1 .= " AND e.preferencia_facturacion = ? ";
            array_push($vartouse, $params['ciclo']);
        }

        if ( isset($params['plan']) && $params['plan'] != '-' ) {
            $query_level1 .= " AND e.plan_id = ? ";
            array_push($vartouse, $params['plan']);
        }


        $query_level2 = "SELECT a.* FROM (" . $query_level1 . ") AS a ";
        $query_level3 = "SELECT a.*, bm.mensaje, u.nombre AS 'empleado_nombre', IF( a.de_tipo = 'E', a.de, a.a ) AS 'empleado_id' FROM ( ".$query_level2." ) a LEFT JOIN bandeja_mensaje bm ON bm.id = a.bandeja_id LEFT JOIN usuario u ON u.id = IF( a.de_tipo = 'E', a.de, a.a )";

        $query_level4 = "SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level3 . ") AS r ";
        $query_level4 .= " ORDER BY r.created_at DESC";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level4 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level4 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = \DB::select(\DB::raw($query_level4), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_facturadetalle_validate($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id FROM factura WHERE empresa_id = ? AND estado <> 'ANULADA'";
        array_push( $vartouse, $params['empresa_id'] );
        $query_level1 = "SELECT * FROM factura_item WHERE factura_id IN ( ".$query_level0." ) AND tipo = 'P' AND anio = ? and mes = ?";
        array_push( $vartouse, $params['anio'] );
        array_push( $vartouse, $params['mes'] );

        $rows = \DB::select(\DB::raw($query_level1), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_crmnota($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT empleado,empresa_id, nota, fecha_creacion, CONCAT(fecha,' ',hora) AS fecha, crm_tipo_id FROM crm c WHERE id > 0 and empresa_id > 0";
        
        if ( isset($params['empresa_id']) && $params['empresa_id'] != '-' && $params['empresa_id'] > 0 ) {
            $query_level0 .= " AND empresa_id = ? ";
            array_push($vartouse, $params['empresa_id']);
        }
        if ( isset($params['crm']) && $params['crm'] != '-' && $params['crm'] > 0) {
            $query_level0 .= " AND crm_tipo_id = ? ";
            array_push($vartouse, $params['crm']);
        }

        if ( isset($params['tipofecha']) ){
            if( $params["tipofecha"] == "programacion" ){
                if (isset($params['fini']) && isset($params['ffin'])) {
                    $query_level0 .= " AND DATE( fecha ) BETWEEN ? AND ? ";
                    array_push($vartouse, $params['fini']);
                    array_push($vartouse, $params['ffin']);
                }
            }else{
                if (isset($params['fini']) && isset($params['ffin'])) {
                    $query_level0 .= " AND DATE( fecha_creacion ) BETWEEN ? AND ? ";
                    array_push($vartouse, $params['fini']);
                    array_push($vartouse, $params['ffin']);
                }
            }
        }



        $query_level1 = "SELECT a.empleado,a.empresa_id, a.nota, a.fecha_creacion, a.fecha, cp.nombre AS crm_tipo FROM (".$query_level0.") a JOIN crm_tipo cp ON a.crm_tipo_id = cp.id ORDER BY a.fecha_creacion DESC ";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        $query_level2 .= " ORDER BY r.fecha_creacion DESC";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_crm($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, empresa_id, nota, fecha_creacion, crm_tipo_id, usuario_id FROM crm WHERE estado = 'A' ";
        
        if ( isset($params['empresa_id']) && $params['empresa_id'] != '-' && $params['empresa_id'] > 0 ) {
            $query_level0 .= " AND empresa_id = ? ";
            array_push($vartouse, $params['empresa_id']);
        }

        if( $params["tipofecha"] == "programacion" ){
            if (isset($params['fini']) && isset($params['ffin'])) {
                $query_level0 .= " AND DATE(fecha) BETWEEN ? AND ? ";
                array_push($vartouse, $params['fini']);
                array_push($vartouse, $params['ffin']);
            }

        }else{
            if (isset($params['fini']) && isset($params['ffin'])) {
                $query_level0 .= " AND DATE(fecha_creacion) BETWEEN ? AND ? ";
                array_push($vartouse, $params['fini']);
                array_push($vartouse, $params['ffin']);
            }
        }

        if (isset($params['estado']) && $params['estado'] != '-') {
            $query_level0 .= " AND estado = ? ";
            array_push($vartouse, $params['estado']);
        }
        if (isset($params['usuario']) && $params['usuario'] != '-') {
            $query_level0 .= " AND usuario_id = ? ";
            array_push($vartouse, $params['usuario']);
        }
        if (isset($params['crm']) && $params['crm'] != '-') {
            $query_level0 .= " AND crm_tipo_id = ? ";
            array_push($vartouse, $params['crm']);
        }

        $query_level1 = "SELECT c.id, c.empresa_id, c.nota, c.fecha_creacion, c.crm_tipo_id, c.usuario_id, e.empresa_nombre, e.preferencia_facturacion FROM (".$query_level0.") c 
            LEFT JOIN empresa e ON c.empresa_id = e.id WHERE e.id IS NOT NULL
        ";
        if (isset($params['ciclo']) && $params['ciclo'] != '-') {
            $query_level1 .= " AND e.preferencia_facturacion = ? ";
            array_push($vartouse, $params['ciclo']);
        }
        if (isset($params['plan']) && $params['plan'] != '-') {
            $query_level1 .= " AND e.plan_id = ? ";
            array_push($vartouse, $params['plan']);
        }

        $query_level2 = "SELECT a.id, a.empresa_id, a.nota, a.fecha_creacion, a.usuario_id, a.crm_tipo_id, a.empresa_nombre, a.preferencia_facturacion, u.nombre as 'usunombre', ct.nombre as 'tiponombre' FROM (".$query_level1.") a 
            LEFT JOIN crm_tipo  ct ON a.crm_tipo_id = ct.id
            LEFT JOIN usuario   u  ON a.usuario_id  = u.id GROUP BY a.empresa_id";


        $query_level3 = "SELECT SQL_CALC_FOUND_ROWS *,(SELECT c2.nota FROM crm c2 WHERE c2.empresa_id = a.empresa_id ORDER BY c2.fecha_creacion DESC LIMIT 1) AS 'contentnota' FROM (" . $query_level2 . ") a ORDER BY a.fecha_creacion DESC ";

        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level3 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level3 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = \DB::select(\DB::raw($query_level3), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_empresa($params)
    {
        $vartouse = [];
        $query_level1 = "SELECT * FROM empresa WHERE 1=1";

        if(isset($params['preferencia_estado']) && $params['preferencia_estado'] !== '-'){
            $query_level1 .= " AND preferencia_estado = ?";
            array_push($vartouse, $params['preferencia_estado']);
        }

        if(isset($params['plan']) && $params['plan'] !== '-'){
            $query_level1 .= " AND plan_id = ?";
            array_push($vartouse, $params['plan']);
        }

        if(isset($params['preferencia_facturacion']) && $params['preferencia_facturacion'] !== '-'){
            $query_level1 .= " AND preferencia_facturacion = ?";
            array_push($vartouse, $params['preferencia_facturacion']);
        }

        if(isset($params['preferencia_fiscal']) && $params['preferencia_fiscal'] !== '-'){
            $query_level1 .= " AND preferencia_fiscal = ?";
            array_push($vartouse, $params['preferencia_fiscal']);
        }

        if(isset($params['central']) && $params['central'] !== '-'){
            $query_level1 .= " AND central = ?";
            array_push($vartouse, $params['central']);
        }

        if(isset($params['del_state']) && $params['del_state'] !== '-'){
            $query_level1 .= " AND del_state = ?";
            array_push($vartouse, $params['del_state']);
        }

        if(isset($params['convenio']) && $params['convenio'] !== '-'){
            $query_level1 .= " AND convenio = ?";
            array_push($vartouse, $params['convenio']);
        }

        if(isset($params['entrevistado']) && $params['entrevistado'] !== '-'){
            $query_level1 .= " AND entrevistado = ?";
            array_push($vartouse, $params['entrevistado']);
        }

        if(isset($params['promo']) && $params['promo'] === 'S'){
            $query_level1 .= " AND promo = ?";
            array_push($vartouse, $params['promo']);
        }

        if(isset($params['year']) && $params['year'] > 0 ){
            $query_level1 .= " AND YEAR(fecha_creacion) = ?";
            array_push($vartouse, $params['year']);
        }

        if(isset($params['month']) && $params['month'] > 0 ){
            $query_level1 .= " AND MONTH(fecha_creacion) = ?";
            array_push($vartouse, $params['month']);
        }

        $col_empresa = "e.id, e.plan_id, e.empresa_nombre, e.preferencia_estado, e.empresa_ruc, e.empresa_rubro, e.preferencia_fiscal, e.entrevistado, e.promo, e.nombre_comercial, CONCAT(e.fac_nombre, ' ', e.fac_apellido) as encargado, e.preferencia_cdr, e.siguiente_fecha_facturacion, e.entrevista_recordatorio, e.passcard, e.central_id, e.central, IF(e.preferencia_facturacion = 'MENSUAL', 'M', 'Q') AS `preferencia_facturacion`, e.convenio, e.del_state";

        $col_extras = "ce.numero, p.nombre AS plan, IF ((SELECT COUNT(id) FROM factura WHERE empresa_id = e.id AND estado = 'PENDIENTE' and DATE(curdate()) >= fecha_vencimiento) > 0, 'S', 'N') AS factura_id";

        $query_level2 = " FROM (" . $query_level1. ") as e JOIN plan as p ON p.id = e.plan_id JOIN central as ce ON e.central_id = ce.id";

        $query_level3 = "SELECT SQL_CALC_FOUND_ROWS * FROM ( SELECT " . $col_empresa . "," . $col_extras . " " . $query_level2 . " ) a WHERE 1=1";

        if(isset($params['search_filter'], $params['search_value']) && !empty($params['search_value'])){
            $query_level3.= " AND a.`".$params['search_filter']."` LIKE ?";
            array_push($vartouse, '%'.$params['search_value'].'%');
        }

        if(isset($params['factura_id']) && $params['factura_id'] !== '-'){
            $query_level3.= ' AND a.factura_id = ? ';
            array_push($vartouse, $params['factura_id']);
        }

        $query_level3 .= " ORDER BY a.empresa_nombre ASC";

        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level3 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level3 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = (\DB::select(\DB::raw($query_level3), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'")));
        return ["rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_correspondencia($params)
    {
        $vartouse = [];
        $query_level1 = "SELECT * FROM correspondencia WHERE id > 0 ";
        if ($params["anio"] != "-" && $params["anio"] > 0 ) {
            $query_level1 .= " AND YEAR(fecha_creacion) = ?";
            array_push($vartouse, $params["anio"]);
        }
        if ($params["mes"] != "-" && $params["mes"] > 0 ) {
            $query_level1 .= " AND MONTH(fecha_creacion) = ?";
            array_push($vartouse, $params["mes"]);
        }
        if ($params["estado"] != "-") {
            $query_level1 .= " AND estado = ?";
            array_push($vartouse, $params["estado"]);
        }
        if (isset($params["filter_search"], $params["filter"]) && !empty($params["filter_search"]) && !empty($params["filter"])) {
            if ($params["filter_search"] == "asunto") {
                $query_level1 .= " AND asunto LIKE ?";
                array_push($vartouse, "%" . $params["filter"] . "%");
            } else if ($params["filter_search"] == "remitente") {
                $query_level1 .= " AND remitente LIKE ?";
                array_push($vartouse, "%" . $params["filter"] . "%");
            }
        }
        if (isset($params['empresa_id']) && !empty($params['empresa_id'])) {
            $query_level1 .= " AND empresa_id = ?";
            array_push($vartouse, $params["empresa_id"]);
        }

        $col_corres = "a.id, a.empresa_id, a.fecha_creacion, IFNULL( a.fecha_entrega, '---' ) AS 'fecha_entrega', a.remitente, a.asunto, a.entregado_a, a.nota, a.estado, a.creado_por, a.entregado_por, a.cc, a.lugar, a.local_id, a.qrcode, a.confirmado, a.updated_at, ";
        $col_empresa = "e.empresa_nombre, e.empresa_ruc";
        $query_level2 = " FROM ( " . $query_level1 . " ) a LEFT JOIN empresa e ON e.id = a.empresa_id WHERE e.id IS NOT NULL ";
        $query_level3 = "SELECT SQL_CALC_FOUND_ROWS * FROM ( SELECT " . $col_corres . " " . $col_empresa . " " . $query_level2 . " ) a ORDER BY a.fecha_creacion DESC";

        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level3 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level3 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = (\DB::select(\DB::raw($query_level3), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'")));
        return ["rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_recado($params)
    {
        $vartouse = [];
        $query_level1 = "SELECT * FROM recado WHERE id > 0 ";
        if ($params["anio"] != "-" && $params["anio"] > 0 ) {
            $query_level1 .= " AND YEAR(fecha_creacion) = ?";
            array_push($vartouse, $params["anio"]);
        }
        if ($params["mes"] != "-" && $params["mes"] > 0 ) {
            $query_level1 .= " AND MONTH(fecha_creacion) = ?";
            array_push($vartouse, $params["mes"]);
        }
        if ($params["estado"] != "-") {
            $query_level1 .= " AND estado = ?";
            array_push($vartouse, $params["estado"]);
        }

        if (isset($params['empresa_id']) && !empty($params['empresa_id'])) {
            $query_level1 .= " AND empresa_id = ?";
            array_push($vartouse, $params["empresa_id"]);
        }

        $col_corres = "a.id, a.empresa_id, a.para, a.contenido_paquete, a.fecha_creacion, IFNULL( a.fecha_entrega, '---' ) AS 'fecha_entrega', a.entregado_a, a.estado, a.creado_por, a.entregado_por, a.lugar,  ";
        $col_empresa = "e.empresa_nombre, e.empresa_ruc";
        $query_level2 = " FROM ( " . $query_level1 . " ) a LEFT JOIN empresa e ON e.id = a.empresa_id WHERE e.id IS NOT NULL ";
        $query_level3 = "SELECT SQL_CALC_FOUND_ROWS * FROM ( SELECT " . $col_corres . " " . $col_empresa . " " . $query_level2 . " ) a ORDER BY a.fecha_creacion DESC";

        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level3 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level3 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = (\DB::select(\DB::raw($query_level3), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'")));
        return ["rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_reserva($params)
    {
        $vartouse = [];/*
        if (isset($params['estado']) && !empty($params['estado'])) {
            $estado = $params['estado'];
        } else {
            $estado = 'A';
        }*/

        $query_level1 = "SELECT * FROM reserva WHERE id > 0 ";

        if(isset($params['fecha']) && !is_null($params['fecha']) && !empty($params['fecha'])){
            $query_level1 .= " AND fecha_reserva = ? ";
            array_push($vartouse, $params['fecha']);
        }

        if(isset($params['anio']) && !is_null($params['anio']) && !empty($params['anio']) && $params['anio'] > 0 ){
            $query_level1 .= " AND YEAR(fecha_reserva) = ? ";
            array_push($vartouse, $params['anio']);
        }

        if(isset($params['mes']) && !is_null($params['mes']) && !empty($params['mes']) && $params['mes'] > 0 ){
            $query_level1 .= " AND MONTH(fecha_reserva) = ? ";
            array_push($vartouse, $params['mes']);
        }


        if(isset($params['estado']) && !is_null($params['estado']) && !empty($params['estado']) && $params['estado'] != '-' ){
            $estado = "";
            if( $params['estado'] == "E" ){
                $query_level1 .= " AND estado IN ('E')";
            }else if( $params['estado'] == "A" ){
                $query_level1 .= " AND estado IN ('A','J')";
            }else if( $params['estado'] == "P" ){
                $query_level1 .= " AND estado IN ('P','C')";
            }
            
            //array_push($vartouse, $estado);
        }
        //array_push($vartouse, $estado);

        if (isset($params['empresa_id']) && !empty($params['empresa_id'])) {
            $query_level1 .= " AND empresa_id = ? ";
            array_push($vartouse, $params['empresa_id']);
        }

        $col_reserv = "a.id, a.empresa_id, a.oficina_id, a.fecha_reserva, a.hora_inicio, a.hora_fin, a.proyector, a.cochera_id, IFNULL( UPPER(a.placa) ,'-') AS 'placa', a.created_at, a.estado, a.movil, a.observacion, a.updated_at, a.creado_por, TIMEDIFF(a.hora_fin,a.hora_inicio) AS 'diferencia', UNIX_TIMESTAMP( TIMESTAMP( a.fecha_reserva, a.hora_inicio ) ) AS 'unix', ";

        $col_empresa = "e.empresa_nombre, e.empresa_ruc,";
        $col_oficina = "o.nombre AS 'oficina_nombre', o.capacidad AS 'oficina_capacidad', o.imagen AS 'oficina_imagen', o.piso_id AS 'oficina_piso', o.tipo AS 'oficina_tipo', o.local_id, o.modelo_id, ";
        $col_modelo = "m.nombre AS 'modelo_nombre',";
        $col_local = "c.nombre AS 'local_nombre',c.direccion AS 'local_direccion',";
        $col_cochera = "co.nombre AS 'cochera_nombre' ";

        $query_level2 = " FROM ( " . $query_level1 . " ) a LEFT JOIN empresa e ON e.id = a.empresa_id  ";
        $query_level2 .= " LEFT JOIN oficina o ";
        $query_level2 .= " LEFT JOIN modelo m ON m.id = o.modelo_id ";
        $query_level2 .= " LEFT JOIN clocal c ON c.id = o.local_id ";
        $query_level2 .= " ON o.id = a.oficina_id";
        $query_level2 .= " LEFT JOIN cochera co ON co.id = a.cochera_id ";
        $query_level2 .= " WHERE e.id IS NOT NULL ";

        if (isset($params['modelo_id']) && !empty($params['modelo_id'])) {
            $query_level2 .= " AND o.modelo_id = ? ";
            array_push($vartouse, $params['modelo_id']);
        }
        if (isset($params['local_id']) && !empty($params['local_id'])) {
            $query_level2 .= " AND o.local_id = ? ";
            array_push($vartouse, $params['local_id']);
        }
        if (isset($params['oficina_id']) && !empty($params['oficina_id'])) {
            $query_level2 .= " AND o.id = ? ";
            array_push($vartouse, $params['oficina_id']);
        }

        $query_level3 = "SELECT SQL_CALC_FOUND_ROWS * FROM ( SELECT " . $col_reserv . " " . $col_empresa . " " . $col_oficina . " " . $col_modelo . " " . $col_local . " " . $col_cochera . " " . $query_level2 . " ) a ORDER BY a.fecha_reserva DESC, a.hora_inicio DESC";

        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level3 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level3 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = (\DB::select(\DB::raw($query_level3), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'")));

        $consumo = "00:00";
        if(isset($params['consumo']) && $params['consumo'] == 'on' && isset($params['empresa_id']) && $params['empresa_id']>0){
            $consumo = DB::table('reserva')->where('empresa_id', $params['empresa_id'])->where('estado','A')->where(DB::raw('YEAR(fecha_reserva)'),$params['anio'])->where(DB::raw('MONTH(fecha_reserva)'), $params['mes'])->value(DB::raw('SEC_TO_TIME(IFNULL(SUM(TIME_TO_SEC(TIMEDIFF(hora_fin,hora_inicio))),0))'));
        }

        return ["rows" => $rows, "total" => $tota[0]->rows, "consumo"=>$consumo];
    }

    public function Q_pagos($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, numero, fecha_creacion, fecha_pago, monto AS 'factura_monto', estado, moneda, comprobante, empresa_id, usuario_pago, fecha_limite as 'fechaingreso' FROM factura a WHERE id > 0 AND estado = 'PAGADA' ";
        if ($params["anio"] != "-" && $params["anio"] > 0) {
            if (isset($params['tipo']) && $params['tipo'] == 'pago') {
                $query_level0 .= " AND YEAR(fecha_pago) = ?";
            } else {
                $query_level0 .= " AND YEAR(fecha_creacion) = ?";
            }
            array_push($vartouse, $params["anio"]);
        }
        if ($params["mes"] != "-" && $params["mes"] > 0) {
            if (isset($params['tipo']) && $params['tipo'] == 'pago') {
                $query_level0 .= " AND MONTH(fecha_pago) = ?";
            } else {
                $query_level0 .= " AND MONTH(fecha_creacion) = ?";
            }
            array_push($vartouse, $params["mes"]);
        }
        $pago_col = " IFNULL(p.monto,0) AS 'monto', p.tipo, IFNULL(p.dif_dep_pos,0) AS 'diferenciapos', IFNULL(p.des_com_pos,0) AS 'comisionpos', IFNULL(p.detraccionD,0) AS 'detracDeposito', IFNULL(p.detraccionE,0) AS 'detracEfectivo', (IFNULL(p.detraccionD,0)+IFNULL(p.detraccionE,0)) AS 'detrac',(IFNULL(p.monto,0)-(IFNULL(p.detraccionD,0)+IFNULL(p.detraccionE,0))-IFNULL(p.des_com_pos,0)) AS 'totalcomision', f.fechaingreso";

        $query_level1 = "SELECT f.id, f.numero, f.fecha_creacion, f.fecha_pago, f.factura_monto, f.estado, f.moneda, f.comprobante, f.empresa_id, f.usuario_pago, po.id AS 'idpos', po.nom_pos, po.id AS 'id_pos', " . $pago_col . " FROM ( " . $query_level0 . " ) f INNER JOIN pago p ON f.id = p.factura_id LEFT JOIN pos  AS po ON p.id_pos = po.id WHERE p.id > 0  ";
        if (isset($params['tipopago']) && $params['tipopago'] != '') {
            $query_level1 .= "AND p.tipo = ? ";
            array_push($vartouse, $params["tipopago"]);
        }
        $cols = "e.id, e.empresa_nombre, e.preferencia_facturacion, a.numero, a.fecha_creacion, a.fecha_pago, a.factura_monto, a.estado, a.moneda, a.comprobante, a.empresa_id, a.usuario_pago, a.idpos,  a.monto, a.tipo, a.diferenciapos, a.comisionpos, a.detracDeposito, a.detracEfectivo, a.detrac, a.totalcomision, pl.nombre as 'nombreplan',a.nom_pos, a.id_pos, a.fechaingreso";
        $query_level2 = "SELECT " . $cols . " FROM (" . $query_level1 . ") a LEFT JOIN empresa e 
            LEFT JOIN plan AS pl ON e.plan_id = pl.id  ON e.id = a.empresa_id WHERE e.preferencia_estado <> 'X'";

        if (isset($params['ciclo']) && $params['ciclo'] != '') {
            $query_level2 .= " AND e.preferencia_facturacion = ? ";
            array_push($vartouse, $params["ciclo"]);
        }
        $query_level3 = "SELECT SQL_CALC_FOUND_ROWS * FROM ( " . $query_level2 . " ) a ORDER BY a.fecha_pago DESC";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level3 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level3 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = (\DB::select(\DB::raw($query_level3), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'")));
        return ["rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_facturacion($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, comprobante, SPLIT_STR( numero, '-', 1 ) as 'serie', SPLIT_STR( numero, '-', 2 ) as 'numero', fecha_creacion, fecha_vencimiento, fecha_limite, monto, estado, moneda, empresa_id, accion, su_state, su_info, mail_send FROM factura WHERE id > 0 ";
        if (isset($params["anio"]) && $params["anio"] != "-"  && $params["anio"] > 0 ) {
            $query_level0 .= " AND YEAR(fecha_creacion) = ?";
            array_push($vartouse, $params["anio"]);
        }
        if (isset($params["mes"]) && $params["mes"] != "-" && $params["mes"] > 0) {
            $query_level0 .= " AND MONTH(fecha_creacion) = ?";
            array_push($vartouse, $params["mes"]);
        }
        if (isset($params["empresa_id"]) && $params["empresa_id"] != "-" && $params["empresa_id"] > 0) {
            $query_level0 .= " AND empresa_id = ?";
            array_push($vartouse, $params["empresa_id"]);
        }

        if (isset($params["estado"]) && $params["estado"] != "" && $params["estado"] != "-") {
            $query_level0 .= " AND estado = ?";
            array_push($vartouse, $params["estado"]);
        }

        $query_level1 = "SELECT f.id, f.comprobante, f.serie, f.numero, f.fecha_limite, f.fecha_creacion, f.fecha_vencimiento, f.monto, f.estado, f.moneda, f.empresa_id, e.empresa_nombre, e.empresa_ruc, e.preferencia_facturacion, e.preferencia_estado, f.accion, f.su_state, f.su_info, f.mail_send FROM (" . $query_level0 . ") f LEFT JOIN empresa e ON e.id = f.empresa_id WHERE e.preferencia_estado <>  'X'";

        if (isset($params["ciclo"]) && $params["ciclo"] != "") {
            $query_level1 .= " AND e.preferencia_facturacion = ?";
            array_push($vartouse, $params["ciclo"]);
        }

        $query_level2 = "SELECT a.id, a.comprobante, a.serie, a.numero, a.fecha_creacion, a.fecha_limite, a.fecha_vencimiento, a.monto, a.estado, a.moneda, a.empresa_id, a.empresa_nombre, a.empresa_ruc, a.preferencia_facturacion, SUM(IFNULL(IF((n.tipo='CREDITO'),(n.precio*-1),(n.precio)),0)) AS 'nota_monto', a.accion, a.su_state, a.mail_send, a.su_info, a.preferencia_estado FROM ( " . $query_level1 . " ) a LEFT JOIN factura_notas n ON n.factura_id = a.id GROUP BY a.id";

        $query_level3 = "SELECT SQL_CALC_FOUND_ROWS a.id, a.comprobante, a.serie, a.numero, DATE(a.fecha_creacion) AS 'fecha_emision', a.fecha_creacion, a.fecha_vencimiento, a.fecha_limite, a.monto, a.estado, a.moneda, a.empresa_id, a.empresa_nombre, a.empresa_ruc, a.preferencia_facturacion, (a.monto+a.nota_monto) AS 'total', a.mail_send, a.su_info, a.su_state, a.nota_monto, CONCAT( a.serie, a.numero ) AS 'docnum', ROUND((a.monto-(a.monto/1.18)),2) as 'base', ROUND((a.monto/1.18),2) as 'igv', a.preferencia_estado  FROM ( " . $query_level2 . " ) a ORDER BY a.numero DESC";

        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level3 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level3 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = (\DB::select(\DB::raw($query_level3), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'")));
        return ["rows" => $rows, "total" => $tota[0]->rows];
    }


    public function Q_facturacion_historial($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, IFNULL( item_id, '' ) AS 'item_id', tipo, observacion, usuario, fecha, factura_id FROM factura_historial WHERE id > 0";

        if ( isset($params['factura_id']) && $params['factura_id'] != '-' && $params['factura_id'] > 0 ) {
            $query_level0 .= " AND factura_id = ? ";
            array_push($vartouse, $params['factura_id']);
        }

        $query_level1 = "SELECT h.*, IFNULL( fi.descripcion, '' ) AS 'descripcion' FROM (".$query_level0.") h LEFT JOIN factura_item fi ON h.item_id = fi.id ORDER BY h.id DESC";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_correspondenciaEmpresa($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, empresa_id, estado FROM correspondencia WHERE id > 0 ";
        if ($params["anio"] != "-" && $params["anio"] > 0 ) {
            $query_level0 .= " AND YEAR(fecha_creacion) = ?";
            array_push($vartouse, $params["anio"]);
        }
        if ($params["mes"] != "-" && $params["mes"] > 0 ) {
            $query_level0 .= " AND MONTH(fecha_creacion) = ?";
            array_push($vartouse, $params["mes"]);
        }
        $col_corres = " a.id, a.empresa_id, a.estado ";
        $col_empresa = "e.empresa_nombre, e.empresa_ruc";

        $query_level1 = "SELECT SUM( IF( ( a.estado = 'P' ),(1),(0) ) ) AS 'pendiente', SUM( IF( ( a.estado = 'B' ),(1),(0) ) ) AS 'eliminado', SUM( IF( ( a.estado = 'E' ),(1),(0) ) ) AS 'entregado', SUM( IF( ( a.estado = 'Z' ),(1),(0) ) ) AS 'confirmado', COUNT(*) AS 'total', a.empresa_id FROM ( " . $query_level0 . " ) a GROUP BY a.empresa_id";
        $query_level2 = " FROM ( " . $query_level1 . " ) a LEFT JOIN empresa e ON e.id = a.empresa_id WHERE e.id IS NOT NULL ";
        if (isset($params['txt'])) {
            $query_level2 .= " AND e.empresa_nombre LIKE '" . $params['txt'] . '%' . "' ";
            array_push($vartouse, $params["txt"]);
        }
        $query_level3 = "SELECT SQL_CALC_FOUND_ROWS * FROM ( SELECT a.empresa_id , e.empresa_nombre, e.empresa_ruc, e.preferencia_estado, a.pendiente, a.eliminado, a.entregado, a.confirmado, a.total  " . $query_level2 . " ) a ORDER BY (a.pendiente+a.eliminado+a.entregado+a.confirmado) DESC, a.confirmado DESC, a.entregado DESC, a.pendiente DESC, a.eliminado DESC";

        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level3 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level3 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = (\DB::select(\DB::raw($query_level3), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'")));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_cdrEmpresa($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT billsec, userfield FROM cdr WHERE userfield is not null ";
        if ($params["anio"] != "-" && $params["anio"] > 0) {
            $query_level0 .= " AND YEAR(calldate) = ?";
            array_push($vartouse, $params["anio"]);
        }
        if ($params["mes"] != "-" && $params["mes"] > 0) {
            $query_level0 .= " AND MONTH(calldate) = ?";
            array_push($vartouse, $params["mes"]);
        }
        if ($params["userfield"] != "") {
            $query_level0 .= " AND userfield = ?";
            array_push($vartouse, $params["userfield"]);
        }

        $query_level1 = "SELECT COUNT( a.billsec ) AS 'llamadas', ROUND( (SUM( a.billsec )/60), 0) AS 'minutos', a.userfield FROM ( " . $query_level0 . " ) a GROUP BY a.userfield";
        $query_level2 = " FROM ( " . $query_level1 . " ) a LEFT JOIN empresa e ON e.preferencia_cdr = a.userfield WHERE e.id IS NOT NULL ";
        $query_level3 = "SELECT SQL_CALC_FOUND_ROWS * FROM ( SELECT e.id AS 'empresa_id' , e.empresa_nombre, e.empresa_ruc, e.preferencia_estado, a.minutos, a.llamadas  " . $query_level2 . " ) a ORDER BY a.llamadas DESC";

        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level3 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level3 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = (\DB::select(\DB::raw($query_level3), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'")));
        return ["rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_feedback($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, sugerencia, fecha_creacion, estado, empresa_id, tipo, movil FROM feedback WHERE id > 0 AND sugerencia <> '' ";

        if ($params["anio"] != "-" && $params["anio"] > 0 ) {
            $query_level0 .= " AND YEAR(fecha_creacion) = ? ";
            array_push($vartouse, $params["anio"]);
        }
        if ($params["mes"] != "-" && $params["mes"] > 0 ) {
            $query_level0 .= " AND MONTH(fecha_creacion) = ? ";
            array_push($vartouse, $params["mes"]);
        }


        if ( isset($params["tipo"]) && $params["tipo"] != "") {
            if ($params["tipo"] === 'S') {
                $query_level0 .= " AND tipo = ? ";
                array_push($vartouse, $params["tipo"]);
            } else if ($params["tipo"] === 'Q') {
                $query_level0 .= " AND tipo = ? ";
                array_push($vartouse, $params["tipo"]);
            }
        }
        $query_level1 = "SELECT e.empresa_nombre, f.movil, f.id, f.sugerencia, f.tipo, f.fecha_creacion, f.estado, f.empresa_id FROM (".$query_level0.") f INNER JOIN empresa e ON f.empresa_id = e.id WHERE e.id IS NOT NULL";

        $query_level2 = "SELECT SQL_CALC_FOUND_ROWS * FROM (".$query_level1.") f ";

        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = (\DB::select(\DB::raw($query_level2), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows' ")));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_facturanualreporte($params)
    {
        $vartouse = [];
        $query_level0 = "
            SELECT n.id, n.empresa_id, n.comprobante, n.numero, n.emision, n.monto, n.estado, n.fecha_pago FROM (
                SELECT id, empresa_id, comprobante, numero, DATE(fecha_creacion) AS 'emision', monto, estado, IFNULL( DATE(fecha_pago), '' ) AS 'fecha_pago' FROM factura 
                WHERE YEAR(fecha_creacion) = ? AND estado <> 'ANULADA' AND ( ( comprobante = 'FACTURA' AND numero <> '' ) OR  comprobante <> 'FACTURA' )
            ) n
            UNION ALL
            SELECT n.id, f.empresa_id, n.comprobante, n.numero, n.emision , n.monto, f.numero AS 'estado', DATE(f.fecha_creacion) AS 'fecha_pago' FROM (
                SELECT id, tipo AS 'comprobante', numero, fecha_emision AS 'emision', IF(tipo='CREDITO',precio*-1,precio) AS 'monto', factura_id  FROM factura_notas WHERE YEAR(fecha_emision) = ?
            ) n LEFT JOIN factura f ON f.id = n.factura_id
        ";
        array_push($vartouse, $params["anio"]);
        array_push($vartouse, $params["anio"]);

        $query_level1 = "
            SELECT 
                f.id, f.empresa_id, f.comprobante, f.numero, f.emision, f.monto, f.estado, f.fecha_pago as 'pago',
                e.empresa_ruc AS 'ruc', e.empresa_nombre AS 'empresa', e.preferencia_estado, e.preferencia_facturacion AS 'ciclo', p.nombre AS 'plan'
            FROM (".$query_level0.") f LEFT JOIN empresa e LEFT JOIN plan p ON p.id = e.plan_id ON e.id = f.empresa_id
            WHERE e.preferencia_estado <> 'X' ORDER BY YEAR(f.emision), MONTH(f.emision), 
            IF((f.comprobante = 'FACTURA'),(1),(IF((f.comprobante = 'BOLETA'),(2),(IF((f.comprobante = 'PROVICIONAL'),(3),(IF((f.comprobante = 'CREDITO'),(4),(IF((f.comprobante = 'DEBITO'),(5),(6)))))))))), f.numero
        ";


        $query_level2 = "SELECT SQL_CALC_FOUND_ROWS * FROM ( ".$query_level1." ) a";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = (\DB::select(\DB::raw($query_level2), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows' ")));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];  
    }

    public function Q_facturasmasivo($params)
    {
        $date = new \DateTime();
        $currentDay = $date->format('d');
        $ciclo = 'QUINCENAL';
        $dateLimit = $date->format('Y') . '-' . $date->format('m') . '-11';
        if ($currentDay > 25 || $currentDay < 10) {
            $ciclo = 'MENSUAL';
            $dateLimit = $date->format('Y') . '-' . $date->format('m') . '-25';
        }
        $vartouse = [];

        /*****-------$query_level1 = "SELECT e.empresa_id, e.empresa_nombre, e.empresa_ruc,   CONCAT( e.fac_nombre,' ',e.fac_apellido ) AS 'fac_nombre', e.fac_email, f.id, f.monto AS 'totalventa',  f.comprobante AS 'documento_tdocemisor',f.fecha_emision, f.fecha_vencimiento, f.moneda, f.estado, f.monto_fisico FROM ( ".$query_level0." ) e LEFT JOIN factura f ON f.empresa_id = e.empresa_id WHERE  f.estado <> 'ANULADA' AND comprobante = 'FACTURA' AND su_state <> 'S' AND YEAR(f.fecha_creacion) = ? AND MONTH(f.fecha_creacion) = ? ";-------*/

        $query_level0 = "SELECT f.id, f.monto AS 'totalventa',  f.comprobante AS 'documento_tdocemisor',f.fecha_emision, f.fecha_vencimiento,f.numero, f.moneda, f.estado, f.monto_fisico, f.empresa_id FROM factura f WHERE  f.estado <> 'ANULADA' AND comprobante = 'FACTURA' AND su_state <> 'S' AND YEAR(f.fecha_creacion) = ? AND MONTH(f.fecha_creacion) = ? "; 
        //$query_level0 .= " AND DATE(f.fecha_creacion) >= ?";
        array_push( $vartouse, $date->format('Y') );
        array_push( $vartouse, $date->format('m') );
        //array_push( $vartouse, $dateLimit );

        $query_level1 = "SELECT id AS empresa_id0, empresa_nombre, empresa_ruc, fac_nombre, fac_apellido, fac_email FROM empresa ";/* WHERE  preferencia_facturacion = ? preferencia_estado = 'A' AND */
        //array_push( $vartouse, $ciclo );
        //GROUP BY e.empresa_id LIMIT 1 // Por una extraña razon, le hicieron group by O_O

        $query_level2 = "SELECT f.*, e.* FROM ( ".$query_level0." ) f LEFT JOIN (".$query_level1.") e ON e.empresa_id0 = f.empresa_id WHERE e.empresa_id0 IS NOT NULL";


        $query_level3 = "SELECT SQL_CALC_FOUND_ROWS * FROM ( ".$query_level2." ) a";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level3 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level3 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = (\DB::select(\DB::raw($query_level3), $vartouse));
        $tota = (\DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows' ")));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];  
    }

    public function Q_reporte_payment($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, tipo, IFNULL( deposito_banco, '' ) AS 'deposito_banco', IFNULL( deposito_cuenta, '' ) AS 'deposito_cuenta', IFNULL( deposito_fecha , DATE(fecha_creacion) ) AS 'deposito_fecha', IFNULL(detalle,'') AS 'detalle', IFNULL( monto, 0 ) as 'monto_pago', factura_id, usuario, IFNULL( id_pos, 0 ) AS 'id_pos', IFNULL( dif_dep_pos, 0 ) AS 'dif_dep_pos', IFNULL( des_com_pos, 0 ) AS 'des_com_pos', IFNULL( detraccionD, 0 ) AS 'detraccionD', IFNULL( detraccionE, 0 ) AS 'detraccionE' FROM pago WHERE id > 0 ";
        
        if ( isset($params['year']) && $params['year'] != '-' ) {
            $query_level0 .= " AND YEAR( fecha_creacion ) = ? ";
            array_push($vartouse, $params['year']);
        }
        if ( isset($params['month']) && $params['month'] > 0 && $params['month'] != '-' ) {
            $query_level0 .= " AND MONTH( fecha_creacion ) = ? ";
            array_push($vartouse, $params['month']);
        }
        if ( isset($params['tipopago']) && $params['tipopago'] != '-' ) {
            $query_level0 .= " AND tipo = ? ";
            array_push($vartouse, $params['tipopago']);
        }


        $query_level1 = "SELECT p.*, e.empresa_ruc, e.empresa_nombre, e.preferencia_estado, f.fecha_emision, f.numero, f.estado, IFNULL( f.monto, 0 ) as 'monto_deuda', f.comprobante, f.fecha_pago FROM ( ".$query_level0." ) p LEFT JOIN factura f LEFT JOIN empresa e ON e.id = f.empresa_id ON f.id = p.factura_id WHERE e.preferencia_estado != 'X' ";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }

        $query_level2 .= " ORDER BY r.deposito_fecha DESC";


        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_reporte_invoicepayed($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, empresa_id, fecha_emision, numero, estado, IFNULL( monto, 0 ) as 'monto_deuda', comprobante, fecha_pago FROM factura WHERE id > 0 AND estado = 'PAGADA'";
        
        if ( isset($params['year']) && $params['year'] != '-' ) {
            $query_level0 .= " AND YEAR( fecha_emision ) = ? ";
            array_push($vartouse, $params['year']);
        }
        if ( isset($params['month']) && $params['month'] > 0 && $params['month'] != '-' ) {
            $query_level0 .= " AND MONTH( fecha_emision ) = ? ";
            array_push($vartouse, $params['month']);
        }


        $query_level1 = "SELECT f.*, e.empresa_ruc, e.empresa_nombre, e.preferencia_estado, IFNULL( p.tipo, '' ) AS 'tipo', IFNULL( p.deposito_banco, '' ) AS 'deposito_banco', IFNULL( p.deposito_cuenta, '' ) AS 'deposito_cuenta', IFNULL( p.deposito_fecha, IFNULL( DATE(p.fecha_creacion), '' ) ) AS 'deposito_fecha', IFNULL( p.detalle, '' ) AS 'detalle', IFNULL( monto, 0 ) as 'monto_pago', IFNULL( p.factura_id, '' ) AS 'factura_id', IFNULL( p.usuario, '' ) AS 'usuario', IFNULL( p.id_pos, '' ) AS 'id_pos', IFNULL( p.dif_dep_pos, '' ) AS 'dif_dep_pos', IFNULL( p.des_com_pos, '' ) AS 'des_com_pos', IFNULL( p.detraccionD, '' ) AS 'detraccionD', IFNULL( p.detraccionE, '' ) AS 'detraccionE'
        FROM ( ".$query_level0." ) f LEFT JOIN empresa e ON e.id = f.empresa_id LEFT JOIN pago p ON f.id = p.factura_id WHERE e.preferencia_estado != 'X'";

        if ( isset($params['tipopago']) && $params['tipopago'] != '-' ) {
            $query_level1 .= " AND p.tipo = ? ";
            array_push($vartouse, $params['tipopago']);
        }

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }
        $query_level2 .= " ORDER BY r.fecha_emision DESC, r.deposito_fecha DESC";
        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_adelantos_pendientes($params)
    {

        $vartouse = [];
        $query_level0 = "SELECT id, descripcion, monto, factura_id, empresa_id, estado, fecha_creacion, fecha_uso FROM adelantos WHERE tipo = 'P'";
        if(isset($params["factura_id"]) && $params["factura_id"] > 0){
            $query_level0 .= " AND factura_id = ? AND estado = 'P' ";
            array_push( $vartouse, $params['factura_id'] );
        }

        if(isset($params["empresa_id"]) && $params["empresa_id"] > 0){
            $query_level0 .= " AND empresa_id = ? AND estado = 'P' ";
            array_push( $vartouse, $params['empresa_id'] );
        }

        $query_level1 = "SELECT a.id, a.monto, a.factura_id, a.empresa_id, f.fecha_pago, a.estado, e.empresa_nombre, f.numero, f.moneda, CONCAT(descripcion,' de ',IF(f.moneda = 'S', 'S/. ', '$. '),a.monto) AS descripcion, a.fecha_creacion, a.fecha_uso  FROM ( ".$query_level0." ) a INNER JOIN factura f ON a.factura_id = f.id INNER JOIN empresa e ON a.empresa_id = e.id WHERE e.preferencia_estado != 'X'";

        if(isset($params["year"]) && $params["year"] > 0){
            $query_level1 .= "  AND YEAR( f.fecha_creacion ) = ? ";
            array_push( $vartouse, $params['year'] );
        }

        if(isset($params["month"]) && $params["month"] > 0){
            $query_level1 .= "  AND MONTH( f.fecha_creacion ) = ? ";
            array_push( $vartouse, $params['month'] );
        }

        if(isset($params["factura_id"]) && $params["factura_id"] > 0){
            $query_level0 .= " LIMIT 1";
        }


        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_sobrantes($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, empresa_id, precio AS precio, fecha_creacion, estado, descripcion FROM facturacion_temporal WHERE ex = 'S' AND estado != 'ANULADO' ";

        if(isset($params["year"]) && $params["year"] > 0){
            $query_level0 .= " AND YEAR( fecha_creacion ) = ? ";
            array_push( $vartouse, $params['year'] );
        }

        if(isset($params["month"]) && $params["month"] > 0){
            $query_level0 .= " AND MONTH( fecha_creacion ) = ? ";
            array_push( $vartouse, $params['month'] );
        }

        $query_level1 = "SELECT e.id, e.empresa_nombre, CONCAT(ft.descripcion,' de ',IF(e.moneda = 'S', 'S/. ', '$. '), -ft.precio) AS descripcion, -ft.precio AS precio, ft.fecha_creacion, ft.estado, e.moneda FROM ( ".$query_level0." ) ft INNER JOIN empresa AS e ON ft.empresa_id = e.id WHERE e.preferencia_estado IN ('A','S')";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_reporte_ownmissing($params)
    {
        $adelantos = $this->Q_adelantos_pendientes($params);
        $sobrantes = $this->Q_sobrantes($params);
        return [ "load" => true, "rows" => [ "adelantos" => $adelantos, "sobrantes" => $sobrantes ] ];
    }

    public function Q_reporte_monthpay($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, empresa_id, comprobante, numero, IFNULL( monto, monto_fisico ) AS 'monto', estado, fecha_emision, IFNULL( DATE(fecha_pago), '' ) AS 'fecha_pago' FROM factura WHERE id > 0";
        
        if ( isset($params['year']) && $params['year'] != '-' && $params['year'] > 0 ) {
            $query_level0 .= " AND YEAR( fecha_emision ) = ? ";
            array_push($vartouse, $params['year']);
        }
        if ( isset($params['month']) && $params['month'] > 0 && $params['month'] != '-' ) {
            $query_level0 .= " AND MONTH( fecha_emision ) = ? ";
            array_push($vartouse, $params['month']);
        }
        
        if ( isset($params['yearpay']) && $params['yearpay'] != '-' && $params['yearpay'] > 0 ) {
            $query_level0 .= " AND YEAR( IFNULL( DATE(fecha_pago), '' ) ) = ? ";
            array_push($vartouse, $params['yearpay']);
        }
        if ( isset($params['monthpay']) && $params['monthpay'] > 0 && $params['monthpay'] != '-' ) {
            $query_level0 .= " AND MONTH( IFNULL( DATE(fecha_pago), '' ) ) = ? ";
            array_push($vartouse, $params['monthpay']);
        }

        $query_level1 = "";
        $query_level1 .= "SELECT f.*, ";
        $query_level1 .= "p.pago_fecha, e.empresa_ruc, e.empresa_nombre, e.preferencia_estado, e.preferencia_facturacion, ";
        $query_level1 .= "SUM( IF( ( p.tipo = 'EFECTIVO' ), ( p.pago_monto), ( 0 ) ) ) AS 'efectivo', ";
        $query_level1 .= "SUM( IF( ( p.tipo = 'EFECTIVO' ), ( 1 ),           ( 0 ) ) ) AS 'efectivo_count', ";
        $query_level1 .= "SUM( IF( ( p.tipo = 'DEPOSITO' ), ( p.pago_monto), ( 0 ) ) ) AS 'deposito', ";
        $query_level1 .= "SUM( IF( ( p.tipo = 'DEPOSITO' ), ( 1 ),           ( 0 ) ) ) AS 'deposito_count',";
        $query_level1 .= "SUM( IF( ( p.deposito_banco = 'BCP' ), ( p.pago_monto), ( 0 ) ) ) AS 'bcp', ";
        $query_level1 .= "SUM( IF( ( p.deposito_banco = 'BCP' ), ( 1 ),           ( 0 ) ) ) AS 'bcp_count', ";
        $query_level1 .= "SUM( IF( ( p.deposito_banco = 'CONTINENTAL' ), ( p.pago_monto), ( 0 ) ) ) AS 'continental', ";
        $query_level1 .= "SUM( IF( ( p.deposito_banco = 'CONTINENTAL' ), ( 1 ),           ( 0 ) ) ) AS 'continental_count' ";
        $query_level1 .= "FROM ( ".$query_level0.") f LEFT JOIN empresa e on e.id = f.empresa_id LEFT JOIN ( SELECT * FROM ( SELECT id, tipo, deposito_banco, deposito_cuenta, factura_id, monto AS 'pago_monto', IFNULL( deposito_fecha, fecha_creacion ) AS 'pago_fecha' FROM pago ";
        if ( isset($params['tipopago'])  && $params['tipopago'] != '-' ) {
            $query_level1 .= "WHERE monto > 0 AND factura_id > 0  AND tipo = ? ";
            array_push($vartouse, $params['tipopago']);
        }
        $query_level1 .= " ) p ) p ON p.factura_id = f.id  WHERE e.preferencia_estado <> 'X' GROUP BY f.id ORDER BY f.comprobante, f.numero";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_reporte_paymentintime($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id FROM empresa WHERE preferencia_estado <> 'X'";
        $query_level1 =  "SELECT YEAR(fecha_creacion) AS 'anio', MONTH(fecha_creacion) AS 'mes',";
        $query_level1 .= "SUM( IF(( estado = 'PAGADA' AND DATE(fecha_pago)   <= DATE(fecha_vencimiento) ),(1),(0)) ) AS 'x1',";
        $query_level1 .= "SUM( IF(( estado = 'PAGADA' AND DATE(fecha_limite) >= DATE(fecha_pago) AND DATE(fecha_pago) > DATE(fecha_vencimiento) ),(1),(0)) ) AS 'x2',";
        $query_level1 .= "SUM( IF(( estado = 'PAGADA' AND DATE(fecha_limite) <  DATE(fecha_pago) ),(1),(0)) ) AS 'x3',";
        $query_level1 .= "SUM( IF(( estado = 'PENDIENTE' ),(1),(0)) ) AS 'x4',";

        $query_level1 .= "SUM( IF(( estado = 'PAGADA' AND DATE(fecha_pago)   <= DATE(fecha_vencimiento) ),(monto),(0)) ) AS 'x1_importe',";
        $query_level1 .= "SUM( IF(( estado = 'PAGADA' AND DATE(fecha_limite) >= DATE(fecha_pago) AND DATE(fecha_pago) > DATE(fecha_vencimiento) ),(monto),(0)) ) AS 'x2_importe',";
        $query_level1 .= "SUM( IF(( estado = 'PAGADA' AND DATE(fecha_limite) <  DATE(fecha_pago) ),(monto),(0)) ) AS 'x3_importe',";
        $query_level1 .= "SUM( IF(( estado = 'PENDIENTE' ),(monto),(0)) ) AS 'x4_importe' ";

        $query_level1 .= "FROM factura WHERE YEAR(fecha_creacion) = ?  AND estado <> 'ANULADA' AND ( ( comprobante = 'FACTURA' AND numero <> '' ) OR ( comprobante <> 'FACTURA' ) ) AND empresa_id IN (".$query_level0.") ";
            array_push($vartouse, $params['year']);
        $query_level1 .= "GROUP BY MONTH(fecha_creacion) ORDER BY MONTH(fecha_creacion)";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_abogados_casos($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT c.id, c.demandado, c.demandante, CONCAT(c.demandado,' ',c.demandante) AS ambos, c.created_at, c.caso, c.estado, c.fecha_cambio, YEAR(c.created_at) AS anio, MONTH(c.created_at) AS mes, c.empresa_id FROM caso c WHERE c.id > 0 ";

        if (isset( $params["anio"] ) && $params["anio"] != '-' && $params["anio"] > 0 ) {
            $query_level0 .= " AND YEAR(c.created_at) = ? ";
            array_push($vartouse, $params["anio"] );
        }
        if (isset( $params["mes"] ) && $params["mes"] != '-' && $params["mes"] > 0 ) {
            $query_level0 .= " AND MONTH(c.created_at) = ? ";
            array_push($vartouse, $params["mes"] );
        }
        if (isset( $params["empresa_id"] ) && $params["empresa_id"]  > 0) {
            $query_level0 .= " AND c.empresa_id = ? ";
            array_push($vartouse, $params["empresa_id"] );
        }

        if (isset( $params["estado"] ) && $params["estado"] != '-') {
            $query_level0 .= " AND c.estado = ? ";
            array_push($vartouse, $params["estado"] );
        }

        if (isset( $params["filtro"] ) && isset( $params["filtrotipo"] ) && $params["filtrotipo"] != '' && $params["filtro"] != '') {
            $query_level0 .= " AND c.".$params["filtrotipo"]." LIKE ? ";
            array_push($vartouse, '%'.$params["filtro"].'%' );
        }

        $query_level1 =  "SELECT c.*, e.empresa_nombre FROM (".$query_level0.") c LEFT JOIN empresa AS e ON c.empresa_id = e.id ";
        $query_level1 .= "WHERE e.preferencia_estado <> 'E' ";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ORDER BY r.id DESC";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_reporte_guarantee($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id AS 'factura_id', empresa_id, comprobante, numero, DATE( IFNULL( fecha_emision, fecha_creacion ) ) AS 'fecha_emision' FROM factura f WHERE estado <> 'ANULADA' ";

        if (isset( $params["anio"] ) && $params["anio"] != '-' && $params["anio"] > 0 ) {
            $query_level0 .= " AND YEAR( IFNULL( fecha_emision, fecha_creacion ) ) = ? ";
            array_push($vartouse, $params["anio"] );
        }
        if (isset( $params["mes"] ) && $params["mes"] != '-' && $params["mes"] > 0 ) {
            $query_level0 .= " AND MONTH( IFNULL( fecha_emision, fecha_creacion ) ) = ? ";
            array_push($vartouse, $params["mes"] );
        }


        $query_level00 = "SELECT id FROM factura f WHERE estado <> 'ANULADA' ";

        if (isset( $params["anio"] ) && $params["anio"] != '-' && $params["anio"] > 0 ) {
            $query_level00 .= " AND YEAR( IFNULL( fecha_emision, fecha_creacion ) ) = ? ";
            array_push($vartouse, $params["anio"] );
        }
        if (isset( $params["mes"] ) && $params["mes"] != '-' && $params["mes"] > 0 ) {
            $query_level00 .= " AND MONTH( IFNULL( fecha_emision, fecha_creacion ) ) = ? ";
            array_push($vartouse, $params["mes"] );
        }

        $query_level1 = "SELECT f.*,  CONCAT( f0.numero,'-',f0.comprobante ) AS 'nfacturaUso', e.empresa_nombre, DATE( IFNULL( f0.fecha_emision, f0.fecha_creacion ) ) AS 'fecha_uso', '' AS 'descripcion', e.preferencia_estado FROM (
            SELECT f.*, fi.factura_item_id, fi.precio AS 'monto_pago' FROM ( ".$query_level0." ) f  
            LEFT JOIN ( 
                SELECT id AS 'factura_item_id', factura_id, precio 
                FROM factura_item WHERE tipo = 'G' AND factura_id IN ( ".$query_level00." )
            ) fi ON fi.factura_id = f.factura_id
            WHERE fi.factura_item_id IS NOT NULL
        ) f
        LEFT JOIN empresa e ON e.id = f.empresa_id
        LEFT JOIN pago p 
            LEFT JOIN factura f0 ON f0.id = p.factura_id
        ON p.pago_factura_id = f.factura_item_id";


        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_notas_lista($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT id, factura_id, SPLIT_STR( numero, '-', 1 ) as 'serie', SPLIT_STR( numero, '-', 2 ) as 'numero', numero as 'docnum', observacion, precio, empleado, tipo, fecha_emision, su_state, su_info, fecha_creacion FROM factura_notas WHERE id > 0";

        if ( isset($params['factura_id']) && $params['factura_id'] != '-' && $params['factura_id'] > 0 ) {
            $query_level0 .= " AND factura_id = ? ";
            array_push($vartouse, $params['factura_id']);
        }

        if ( isset($params['anio']) && $params['anio'] != '-' && $params['anio'] > 0 ) {
            $query_level0 .= " AND YEAR( fecha_emision ) = ? ";
            array_push($vartouse, $params['anio']);
        }

        if ( isset($params['mes']) && $params['mes'] != '-' && $params['mes'] > 0 ) {
            $query_level0 .= " AND MONTH( fecha_emision ) = ? ";
            array_push($vartouse, $params['mes']);
        }

        if ( isset($params['tiponota']) && $params['tiponota'] != '-') {
            $query_level0 .= " AND tipo = ? ";
            array_push($vartouse, $params['tiponota']);
        }
        

        $query_level1 = "SELECT n.*, f.numero as 'docmod_numero', f.fecha_emision as 'docmod_emision', f.empresa_id, e.empresa_ruc, e.empresa_nombre FROM (".$query_level0.") n LEFT JOIN factura f LEFT JOIN empresa e ON e.id = f.empresa_id ON f.id = n.factura_id WHERE f.id IS NOT NULL";

        if ( isset($params['empresa_id']) && $params['empresa_id'] != '-' && $params['empresa_id'] > 0 ) {
            $query_level1 .= " AND f.empresa_id = ? ";
            array_push($vartouse, $params['empresa_id']);
        }

        $query_level1 .= " ORDER BY n.fecha_emision DESC ";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_dashboard_empresasregistradas($params)
    {
        $vartouse = [];

        $query_level0 = "SELECT id, empresa_nombre, preferencia_estado, fecha_creacion, plan_id, asesor, preferencia_facturacion FROM empresa WHERE preferencia_estado <> 'X' AND YEAR(fecha_creacion) = ? ";
        array_push($vartouse, $params['anio']);

        if ( isset($params['mes']) && $params['mes'] > 0 && $params['mes'] != '-' ) {
            $query_level0 .= " AND MONTH( fecha_creacion ) = ? ";
            array_push($vartouse, $params['mes']);
        }
        if ( isset($params['ciclo']) && $params['ciclo'] != '-' ) {
            $query_level0 .= " AND preferencia_facturacion = ? ";
            array_push($vartouse, $params['ciclo']);
        }
        if ( isset($params['plan_id']) && $params['plan_id'] > 0 && $params['plan_id'] != '-' ) {
            $query_level0 .= " AND plan_id = ? ";
            array_push($vartouse, $params['plan_id']);
        }
        if ( isset($params['asesor']) && $params['asesor'] != '-' && strtoupper($params['asesor']) != 'TODOS LOS ASESORES' ) {
            $query_level0 .= " AND asesor = ? ";
            array_push($vartouse, $params['asesor']);
        }

        $query_level1 = "SELECT e.*, p.nombre AS plan, CONCAT( c.fecha_inicio,' hasta ',c.fecha_fin) AS contrato, IF( ( YEAR(c.fecha_inicio) <> YEAR(fecha_creacion) OR MONTH(c.fecha_inicio) <> MONTH(fecha_creacion) ), (1), (0) ) AS 'audit' FROM ( ".$query_level0." ) e INNER JOIN contrato AS c ON e.id = c.empresa_id INNER JOIN plan AS p ON e.plan_id = p.id ORDER BY e.asesor, e.fecha_creacion DESC";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = \DB::select(\DB::raw( $query_level2), $vartouse );
        $tota = \DB::select(\DB::raw( "SELECT FOUND_ROWS() AS 'rows'" ) );
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_dashboard_empresascontrato($params)
    {        
        $vartouse = [];
        $query_level0 = "SELECT empresa_id, fecha_inicio, fecha_fin, DATEDIFF( fecha_fin, CURDATE() ) AS 'dif' FROM contrato WHERE estado = 'VIGENTE' AND DATEDIFF( fecha_fin, CURDATE() ) BETWEEN -30 AND 60";
        $query_level1 = "SELECT c.*, e.empresa_nombre, e.preferencia_facturacion FROM ( ".$query_level0." ) c INNER JOIN empresa AS e ON c.empresa_id = e.id WHERE e.preferencia_estado IN ('A','S')  ORDER BY e.preferencia_facturacion, c.dif";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = \DB::select(\DB::raw( $query_level2), $vartouse );
        $tota = \DB::select(\DB::raw( "SELECT FOUND_ROWS() AS 'rows'" ) );
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

    public function Q_dashboard_empresahistorial($params)
    {
        $vartouse = [];
        $query_level0 = "SELECT MAX(id) AS 'id' FROM empresa_historial WHERE YEAR(fecha) = ?  ";
        array_push($vartouse, $params['anio']);
        if ( isset($params['mes']) && $params['mes'] > 0 && $params['mes'] != '-' ) {
            $query_level0 .= " AND MONTH( fecha ) = ? ";
            array_push($vartouse, $params['mes']);
        }

        $query_level0 .= " GROUP BY empresa_id";

        $query_level1 = "SELECT eh1.*, eh2.empresa_id, eh2.estado, eh2.observacion, eh2.fecha, eh2.empleado, e.empresa_nombre, e.asesor, CONCAT(co.fecha_inicio,' hasta ',co.fecha_fin) AS contrato, e.preferencia_estado  FROM ( ".$query_level0." ) eh1 LEFT JOIN empresa_historial eh2 LEFT JOIN empresa e LEFT JOIN contrato co ON e.id = co.empresa_id ON e.id = eh2.empresa_id ON eh2.id = eh1.id WHERE e.id IS NOT NULL and e.preferencia_estado <> 'X' ";

        if ( isset($params['ciclo']) && $params['ciclo'] != '-' ) {
            $query_level1 .= " AND e.preferencia_facturacion = ? ";
            array_push($vartouse, $params['ciclo']);
        }
        $query_level1 .= " ORDER BY e.preferencia_estado,  eh2.fecha DESC";
        
        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }

        $rows = \DB::select(\DB::raw( $query_level2), $vartouse );
        $tota = \DB::select(\DB::raw( "SELECT FOUND_ROWS() AS 'rows'" ) );
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }


    public function Q_invitado_eventos( $params ){
        $vartouse = [];
        $query_level0 = "SELECT id FROM reserva WHERE fecha_reserva = ?";
        array_push( $vartouse, $params['fecha'] );
        $query_level1 = "SELECT reserva_id FROM reserva_invitado WHERE reserva_id IN ( ".$query_level0." ) AND dni = ?";
        array_push( $vartouse, $params['dni'] );
        $query_level2 = "SELECT a.*, e.empresa_nombre AS 'empresa', o.nombre_o AS 'oficina', l.nombre AS 'local', IFNULL(o.capacidad,0) AS 'capacidad', IFNULL( ( SELECT COUNT(*) FROM reserva_invitado i WHERE i.reserva_id = a.id AND asistencia = 1 AND estado = 'A' ),0 ) as 'presentes'   FROM ( SELECT id, empresa_id, oficina_id, hora_inicio, hora_fin, nombre AS 'evento', silla, mesa, audio FROM reserva WHERE id IN ( ".$query_level1." ) ) a LEFT JOIN empresa e ON e.id = a.empresa_id LEFT JOIN oficina o LEFT JOIN clocal l ON l.id = o.local_id ON o.id = a.oficina_id WHERE o.modelo_id IN (2,3)";
        $query_level_1 = "SELECT nomape, email, movil FROM reserva_invitado WHERE reserva_id IN ( ".$query_level0." ) AND dni = ?";
        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        $pers = \DB::select(\DB::raw($query_level_1), $vartouse);
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows, "persona" => $pers ];
    }

    public function Q_invitado( $params ){
        $vartouse = [];
        $query_level0 = "SELECT dni, nomape, email, movil, updated_at, estado, updated_at, asistencia, nuevo FROM reserva_invitado WHERE reserva_id = ?";

        array_push( $vartouse, $params['reserva_id'] );
        $rows = \DB::select(\DB::raw($query_level0), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows ];
    }


    public function Q_reporte_invitados($params){

        $vartouse = [];
        $query_level0 ="SELECT id AS 'reserva_id', empresa_id, oficina_id, fecha_reserva, hora_inicio, hora_fin, nombre AS 'evento_nombre' FROM reserva WHERE estado <> 'E' ";

        $query_level0 .= " AND empresa_id = ?";
        array_push($vartouse, \Auth::user()->id);

        if (isset( $params["anio"] ) && $params["anio"] != '-' && $params["anio"] > 0 ) {
            $query_level0 .= " AND YEAR( fecha_reserva )  = ?   ";
            array_push($vartouse, $params["anio"] );
        }
        if (isset( $params["mes"] ) && $params["mes"] != '-' && $params["mes"] > 0 ) {
            $query_level0 .= " AND MONTH( fecha_reserva ) = ?   ";
            array_push($vartouse, $params["mes"] );
        }
        if (isset( $params["reserva_id"] ) && $params["reserva_id"] != '-' && $params["reserva_id"] > 0 ) {
            $query_level0 .= " AND reserva_id = ?   ";
            array_push($vartouse, $params["reserva_id"] );
        }

        $query_level1 = "
            SELECT 
                a.*, 
                e.empresa_nombre, e.empresa_ruc, 
                IFNULL( ( SELECT CONCAT( SUM( IF((asistencia > 0),(1),(0)) ) , ' / ', COUNT(*) ) FROM reserva_invitado i WHERE i.reserva_id = a.reserva_id ), '-' ) AS 'invitados' 
            FROM (
                SELECT a.*, l.nombre AS 'local_nombre', o.nombre, o.piso_id, o.capacidad FROM (
                    ".$query_level0."
                ) a LEFT JOIN oficina o 
                    LEFT JOIN clocal l ON l.id = o.local_id
                ON o.id = a.oficina_id
                WHERE o.modelo_id <> 1
            ) a LEFT JOIN empresa e ON e.id = a.empresa_id";

        $query_level2 = " SELECT SQL_CALC_FOUND_ROWS * FROM (" . $query_level1 . ") AS r ";
        if (isset($params["limite"])) {
            if (isset($params["pagina"]) && $params["pagina"] > 0) {
                $query_level2 .= " LIMIT " . (($params["pagina"] - 1) * $params["limite"]) . "," . $params["limite"];
            } else {
                $query_level2 .= " LIMIT " . $params["limite"];
            }
        }
        $rows = \DB::select(\DB::raw($query_level2), $vartouse);
        $tota = \DB::select(\DB::raw("SELECT FOUND_ROWS() AS 'rows'"));
        return ['load' => true, "rows" => $rows, "total" => $tota[0]->rows];
    }

}
?>