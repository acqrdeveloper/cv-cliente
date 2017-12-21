<?php
/**
 * Created by PhpStorm.
 * User: aquispe
 * Date: 2017/05/18
 * Time: 15:42
 */

namespace CVClient\Http\Controllers\CV;

use Carbon\Carbon;
use CVClient\Http\Controllers\Controller;
use CVClient\Http\Controllers\ConvertirNumeroALetra;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use PDOException;

class pdfController extends Controller
{

    function contratoPDF($id, $attach = 0, $filename = 'contrato.pdf')
    {
        set_time_limit(120);//ampliamos a 2 minutos el limite de tiempo de conversion a PDF

        // peticion GET
        $arrAll = $this->getAllDataEmpresa($id);
        $dataServicioExtraEmpresa = $this->getDataEmpresaServicioExtra($id);//unico arreglo
        $promoActiva = $this->getPromo150($id);//unico arreglo

        $dataEmpresa = $arrAll[0];
        $dataRepresentante = $arrAll[1][0];
        $dataEmpresaCoworking = $arrAll[10][0];

        $totalServicioExtra = 0;
        $totalPlan = 0;
        $dsctoGarantiaExtra = 0;
        $totalGarantia = 0;
        $totalCoworking = 0;
        $planCoworkings = [25, 28];
        $isCoworking = false;
        $setTitle = '';
        $messagePromocion = '';

        // procesando datos
        $lista = json_decode($dataRepresentante['telefonos'], true);
        if ($lista) {
            $phones = '';
            foreach ($lista as $key) {
                $phones .= $key . ' / ';
            }
            $dataRepresentante['telefonos'] = substr($phones, 0, strlen($phones) - 3);
        }

        if ($dataEmpresa['plan_id'] == 20) {
            $totalServicioExtra = 0;
        } else {
            foreach ($dataServicioExtraEmpresa as $value) {
                $totalServicioExtra += $value->precio;
                // Elizabeth dijo que todos los servicios extras que descuentan al plan,
                // también afecte a la garantía.
                if ($value->precio < 0) {
                    $dsctoGarantiaExtra = $value->precio;
                }
            }
        }

        // aplicamos la resta de los servicios extras
        // APLICA CALCULO TOTAL PLAN
        if ($dataEmpresa['plan_id'] == 25) {
            $totalCoworking = (float)$dataEmpresa['precio'] * (int)$dataEmpresaCoworking['cant_numero'];//Ejemplo : 649 * 3 = 1947 //aplicando multiplicando de modulos
            $totalPlan = $totalCoworking + (float)$totalServicioExtra;//1298 + -594.72 //aplicando descuento
        } else {
            $totalPlan = $dataEmpresa['precio'] + (float)$totalServicioExtra;//649 + -276.36 = 324.64
        }

        // APLICA CALCULO TOTAL GARANTIA
        if ($dataEmpresa['plan_id'] == 11) {
            $totalGarantia = $dataEmpresa['precio'] + (float)$totalServicioExtra;
        } elseif ($dataEmpresa['plan_id'] == 25) {
            $totalGarantia = $totalCoworking;//649*2
        } else {
            $totalGarantia = $dataEmpresa['precio'];//649
        }


        if ($dataEmpresa['plan_id'] == $planCoworkings[0] || $dataEmpresa['plan_id'] == $planCoworkings[1]) {
            $setTitle .= ' - COWORKING';
            $isCoworking = true;
        }

        $promo_gratis = isset($_GET['promo_gratis']) ? utf8_decode('Promoción 7mo mes gratis') : '';
        if (!empty($promoActiva[0]) && $promoActiva[0]->prom_centros == 'S') {// si aplica promo
            $totalPromocion = (float)$promoActiva[0]->monto_prom_centros + (float)$totalServicioExtra;
            $totalPlan = $totalPromocion;
            $messagePromocion = ' - promocion de S/.' . number_format($totalPromocion, 2) . ' solo por el primer mes';
        } else {// si no aplica promo
            $messagePromocion = $promo_gratis;
        }

        ////////////////////////////////////////////////////  HTML /////////////////////////////////////////////////////

        $dataEmpresa['pago_servicio'] = $totalPlan;
        $dataEmpresa['cuarto_mes'] = $dataEmpresa['precio'];
        $dataEmpresa['garantia'] = $totalGarantia;
        $dataEmpresa['fecha_plazo'] = Carbon::parse($dataEmpresa['fecha_inicio'])->format('d/m/Y') . ' al ' . Carbon::parse($dataEmpresa['fecha_fin'])->format('d/m/Y');
        $months = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
        define('DATE_FIRMA', 'Lima, ' . (date('d') . ' de ' . $months[date('n') - 1] . ' del ' . date('Y')));

        // logica
        $COMITENTE = [
            'razon_social' => $dataEmpresa['empresa_nombre'],
            'nombre_comercial' => $dataEmpresa['nombre_comercial'],
            'ruc' => $dataEmpresa['empresa_ruc'],
            'actividad' => $dataEmpresa['empresa_rubro'],
            'poderes' => $dataEmpresa['preferencia_fiscal_nro_partida'] == '0' ? '-' : $dataEmpresa['preferencia_fiscal_nro_partida'],
            'domicilio' => $dataEmpresa['empresa_direccion'],
        ];

        $REPRESENTANTE = [
            'nombre_apellido' => $dataRepresentante['nombre'] . ' ' . $dataRepresentante['apellido'],
            'dni' => $dataRepresentante['dni'],
            'domicilio' => $dataRepresentante['domicilio'],
            'email' => strtoupper($dataRepresentante['correo']),
            'telefono' => $dataRepresentante['telefonos'],
        ];

        $CLIENTE_ENCARGADO = [
            'nombre_apellido' => $dataRepresentante['nombre'] . ' ' . $dataRepresentante['apellido'],
            'dni' => $dataRepresentante['dni'],
            'domicilio' => $dataRepresentante['domicilio'],
            'email' => strtoupper($dataRepresentante['correo']),
            'telefono' => $dataRepresentante['telefonos'],
        ];

        $BENEFICIOS_PLAZO = [
            'plazo' => $dataEmpresa['fecha_plazo'],
            'pago_servicio' => 'S/.' . number_format($dataEmpresa['pago_servicio'], 2),
            'letra_pago_servicio' => '(' . $this->fnConvertLetter((float)$dataEmpresa['pago_servicio']) . ')' . $messagePromocion,
            'cuarto_mes' => 'S/.' . number_format($dataEmpresa['cuarto_mes'], 2),
            'letra_cuarto_mes' => '(' . $this->fnConvertLetter((float)$dataEmpresa['cuarto_mes']) . ')',
            'direccion_servicio' => $dataEmpresaCoworking['nombre'],
            'modulos_asignado' => $dataEmpresaCoworking['numero'],
            'garantia' => 'S/.' . number_format($dataEmpresa['garantia'], 2),
            'letra_garantia' => '(' . $this->fnConvertLetter((float)$dataEmpresa['garantia']) . ')',
            'fecha_pago' => $dataEmpresa['preferencia_facturacion'],
        ];

        $viewHtml = view('contrato', compact('dataEmpresa', 'COMITENTE', 'REPRESENTANTE', 'CLIENTE_ENCARGADO', 'BENEFICIOS_PLAZO', 'isCoworking', 'setTitle', 'DATE_FIRMA'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $config = [
            'attachment' => $attach,
            'hoja' => 'A4',
            'filename' => $filename,
            'orientation' => 'P',
        ];
        $this->fnGeneratePDF($dompdf, $viewHtml, $config);
    }

    function fnConvertLetter($num)
    {
        return ConvertirNumeroALetra::convertir((string)$num, 'soles', 'centimos');
    }

    function getAllDataEmpresa($id)
    {
        $pdo = DB::connection()->getPdo();

        $smt = $pdo->prepare('CALL USP_DETALLE_EMPRESA_2(?)');
        $smt->execute([$id]);

        $dataEmpresa = $smt->fetch();//devuelve una fila
        $smt->nextRowset();//hace un salto y va a ejecutar el siguiente

        $dataRepresentante = $smt->fetchAll();//devuelve todas las filas
        $smt->nextRowset();

        $dataServiciosExtra = $smt->fetchAll();
        $smt->nextRowset();

        $empleados = $smt->fetchAll();
        $smt->nextRowset();

        $saldos = $smt->fetchAll();
        $smt->nextRowset();

        $opciones = $smt->fetchAll();
        $smt->nextRowset();

        $cobros = $smt->fetchAll();
        $smt->nextRowset();

        $news_plans = $smt->fetchAll();
        $smt->nextRowset();

        $promo = $smt->fetch();
        $smt->nextRowset();

        $promoPrize = $smt->fetchColumn(0);
        $smt->nextRowset();

        $listaEmpresasCoworking = $smt->fetchAll();
        $smt->nextRowset();

        $smt->closeCursor();

        return array($dataEmpresa, $dataRepresentante, $dataServiciosExtra, $empleados, $saldos, $opciones, $cobros, $news_plans, $promo, $promoPrize, $listaEmpresasCoworking);

    }

    function getPromo150($id)
    {

        try {
            $arrParams = [];
            $sql = "SELECT prom_centros, monto_prom_centros FROM promociones WHERE empresa_id = ? AND prom_centros = 'S' ";
            array_push($arrParams, $id);
            $data = DB::select(DB::raw($sql), $arrParams);

            return $data;

        } catch (PDOException $e) {
            die ($e->getMessage());
        }
    }

    function getDataEmpresaServicioExtra($id)
    {
        try {
            $arrParams = [];
            $sql = "SELECT se.nombre, se.precio FROM empresa_servicio_extra ese JOIN servicio_extra se ON ese.servicio_extra_id = se.id WHERE empresa_id = ? ";
            array_push($arrParams, $id);
            $data = DB::select(DB::raw($sql), $arrParams);

            return $data;
        } catch (PDOException $e) {
            die ($e->getMessage());
        }
    }

}