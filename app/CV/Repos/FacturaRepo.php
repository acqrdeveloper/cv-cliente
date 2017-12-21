<?php
namespace CVClient\CV\Repos;
ini_set('max_execution_time', 3000);
use CVClient\CV\Models\Pago;
use CVClient\CV\Models\Plan;
use CVClient\CV\Models\RecursoPeriodo;
use CVClient\CV\Models\Reserva;
use CVClient\CV\Models\Factura;
use CVClient\CV\Models\FacturaNota;
use CVClient\CV\Models\FacturaItem;
use CVClient\CV\Models\FacturaHistorial;

use CVClient\CV\Repos\EmpresaRepo;
use CVClient\Common\Repos\SessionRepo;
use CVClient\Common\Repos\QueryRepo;
use CVClient\Common\Repos\PDFRepo;

use CVClient\CV\Repos\ConfiguracionRepo;
class FacturaRepo{

    public function payment_detail($factura_id)
    {
        $factura = Factura::where( "id", $factura_id )->first();
        $pago    = Pago::where( "factura_id", $factura_id )->get();
        $adelant = ( new QueryRepo )->Q_adelantos_pendientes( array( "factura_id" => $factura_id ) );
        if( $adelant["load"] ){
            $adelant = $adelant["rows"];
        }else{
            $adelant = [];
        }
        return [ "data" => [ "factura" => $factura, "pago" => $pago, "adelantos" => $adelant  ] ];
    }

    public function search( $getparams )
    {
        return  ( new QueryRepo )->Q_facturacion( $getparams );
    }

    public function getone($factura_id)
    {
        $comprobante = $this->factura_by_id($factura_id);
        $detalle     = $this->factura_item($factura_id);
        return [ "comprobante" => $comprobante, "detalle" => $detalle];
    }

    public function factura_detalle($id)
    {
        return array( "item" => $this->factura_item($id), "notas" => $this->factura_nota($id)["rows"] );
    }

    public function factura_item($id)
    {
        $items = FacturaItem::where( 'factura_id', $id )->where( 'estado', 'A' )->select(\DB::raw("id, descripcion, descripcion_sunat, precio AS precioconimpuesto, ROUND(precio/1.18, 2) AS preciosinimpuesto, is_nota, warranty, anio, mes, tipo, custom_id, precio"))->get();
        return $items;
    }

    public function factura_nota($id)
    {
        return ( new QueryRepo )->Q_notas_lista( array( "factura_id" => $id ) );
    }


    public function factura_by_id($id)
    {
        return Factura::where( 'id', $id )->first();
    }

    public function getPlanParam( $plan_id )
    {
        return Plan::where( "id", $plan_id )->first();
    }

    public function report_pagos( $anio, $mes, $getparams )
    {
        $getparams["anio"] = $anio;
        $getparams["mes"] = $mes;
        return ( new QueryRepo )->Q_pagos( $getparams );
    }

    public function report_facturacion( $anio, $mes, $getparams )
    {
        $getparams["anio"] = $anio;
        $getparams["mes"] = $mes;
        return ( new QueryRepo )->Q_facturacion( $getparams );
    }

    public function facturacion_empresas( $anio, $mes, $ciclo )
    {
        $nextnumero = ( new ConfiguracionRepo )->GetValor("NUMBER_INVOICE");
        return [
            "nextnumero" => $nextnumero["valor"],
            "rows" => ( new SessionRepo )->CallRaw( "mysql", "AL_FACTURACION_EMPRESAS", array( $anio, $mes, $ciclo ) )
        ];
    }


    public function nota_search( $params ){
        return ( new QueryRepo )->Q_notas_lista( $params );
    }

    public function factura_historial($id)
    {
        return ( new QueryRepo )->Q_facturacion_historial( array( "factura_id" => $id ) );
    }


    public function comprobantePDF( $receptor_ruc, $documento, $serie, $numero )
    {
        $pdf = array("load" => false, "message" => "Documento No encontrado" );
        $empresa = (array)json_decode( ( new EmpresaRepo )->getByNDOC( $receptor_ruc ) );
        if( !empty( $empresa ) ){
            $config = (array)json_decode( ( new ConfiguracionRepo )->GetValor('SUNAT_PARAMS')["valor"] );
            $documento = str_replace( "NOTA DE ", "", $documento );
            if( $documento == 'FACTURA' ){
                $comp = (array)json_decode( Factura::where( "comprobante", $documento )->where( "numero", $serie."-".str_pad( $numero, 5, "0", STR_PAD_LEFT ) )->where( "empresa_id", $empresa["id"] )->first() );
                if(!empty($comp)){
                    $comp['documento_tdocemisor'] = $documento;
                    $comp['empresa_ruc'] = $receptor_ruc;
                    $comp['serie'] = explode( "-", $comp['numero'] )[0];
                    $comp['numero'] = str_replace( $comp["serie"]."-", "", $comp["numero"] );
                    list( $result, $numberValue, $documento_serie, $documento_numero, $values, $detalle ) = $this->factura_config( ($comp+$empresa), $config);
                    list( $loadedPDFfile, $pdf ) = $this->factura_pdf_prepare( ($comp+$empresa), $detalle, $config  );
                    if( $loadedPDFfile ){
                        $pdf = ["load" => true, "data" => $pdf];//landscape
                    }
                }
            }elseif( $documento == 'CREDITO' || $documento == 'DEBITO' ){
                $comp = (array)json_decode( FacturaNota::where( "tipo", $documento )->where( "numero", $serie."-".str_pad( $numero, 5, "0", STR_PAD_LEFT ) )->first() );
                if(!empty($comp)){
                    $compmod = (array)json_decode( Factura::where( "id", $comp["factura_id"] )->first() );
                    $comp['docmod']               = $compmod["numero"];
                    $comp['docmodemision']        = $compmod["fecha_emision"];
                    $comp['documento_tdocemisor'] = "NOTA DE ".$documento;
                    $comp['empresa_ruc']          = $receptor_ruc;
                    $comp['serie']                = explode( "-", $comp['numero'] )[0];
                    $comp['numero']               = str_replace( $comp["serie"]."-", "", $comp["numero"] );
                    list( $result, $numberValue, $documento_serie, $documento_numero, $values, $detalle ) = $this->nota_config( ($comp+$empresa), $config);
                    list( $loadedPDFfile, $pdf ) = $this->factura_pdf_prepare( ($comp+$empresa), $detalle, $config  );
                    if( $loadedPDFfile ){
                        $pdf = ["load" => true, "data" => $pdf];//landscape
                    }
                }
            }
        }
        return $pdf;
    }


    //ESTRUCTURA PDF
    private function factura_pdf_prepare( $comprobante, $detalle, $config )
    {
        $montoTotal       = 0.00;
        $montoUnitario    = 0.00;
        $subTotalVentas   = 0.00;
        $descuento        = 0.00;
        $subWarrantyTotal = 0.00;
        $warrantyTotal    = 0.00;
        $descWarranty     = '';
        foreach ($detalle as $key) {
            if (isset($key['warranty']) && $key['warranty'] == 'S') {
                // Para crear otra linea de descripcion en la factura, si tiene un item de garantía
                $subWarrantyTotal = $key['preciosinimpuesto'];
                $warrantyTotal    = $key['precioconimpuesto'];
                $descWarranty     = $key['descripcion_sunat'];
            } else {
                $subTotalVentas += $key['preciosinimpuesto'];
                $montoTotal     += $key['precioconimpuesto'];
            }
        }
        $montoUnitario = round($montoTotal / 1.18, 2);
        $datos = array( $montoTotal, $montoUnitario, $subTotalVentas, $descuento, $subWarrantyTotal, $warrantyTotal, $descWarranty );
        $html = ( new PDFRepo )->HTML_comprobante( $comprobante, $detalle, $config, $datos );
        $pdf = ( new PDFRepo )->PDF_HTML( $html );
        //return $pdf->setPaper('a4', 'portrait')->setWarnings(false)->download();
        return array( true, $pdf );
        //->stream();//->download();
    }

    private function factura_config( $result, $config, $documento_numero = '' )
    {
        $detalle = $this->factura_item( $result["id"] );

        $data = [];
        $detalleValues = [];
        $totalventa = 0;
        $totalMontoPagar = 0;
        //Importe
        $totalWarrantyVenta = 0;
        $totalWarrantyMontoPagar = 0;
        $descWarranty = '';

        foreach ($detalle as $key) {
            if ($key['warranty'] == 'S') {
                $totalWarrantyMontoPagar = $key['precioconimpuesto'];
                $descWarranty = $key['descripcion_sunat'];
            } else {
                $totalMontoPagar += $key['precioconimpuesto'];
            }
        }
        $totalventa = round($totalMontoPagar / 1.18, 2);
        $importeIgv = $totalMontoPagar - $totalventa;

        $detalleValues[0]['cantidad'] = 1;
        $detalleValues[0]['medida'] = 'ZZ';
        $detalleValues[0]['precioconimpuesto'] = $totalMontoPagar;
        $detalleValues[0]['preciosinimpuesto'] = $totalventa;
        $detalleValues[0]['preciototal'] = $totalMontoPagar;
        $detalleValues[0]['precioventatipo'] = '01';
        $detalleValues[0]['descripcion'] = isset($detalle[0]['descripcion_sunat']) ? $detalle[0]['descripcion_sunat'] : 'SERVICIO EN OFICINAS VIRTUALES';
        $detalleValues[0]['customcode'] = 1;
        $detalleValues[0]['tipo_igv'] = '10';
        $detalleValues[0]['igv'] = $importeIgv;

        if ($totalWarrantyMontoPagar > 0) {
            $totalWarrantyVenta = round($totalWarrantyMontoPagar / 1.18, 2);
            $importeIgv = $totalWarrantyMontoPagar - $totalWarrantyVenta;

            $itemWarranty = [
                'cantidad' => 1,
                'medida' => 'ZZ',
                'preciosinimpuesto' => $totalWarrantyVenta,
                'precioconimpuesto' => $totalWarrantyMontoPagar,
                'preciototal' => $totalWarrantyMontoPagar,
                'precioventatipo' => '01',
                'descripcion' => $descWarranty != '' ? $descWarranty : 'SERVICIO EN OFICINAS VIRTUALES - GARANTIA',
                'customcode' => 1,
                'tipo_igv' => '10',
                'igv' => $importeIgv,
            ];
            $detalleValues[1] = $itemWarranty;

            $totalMontoPagar = $totalMontoPagar + $totalWarrantyMontoPagar;
            $totalventa = round($totalMontoPagar / 1.18, 2);
            $importeIgv = $totalMontoPagar - $totalventa;
        }

        $documento_serie = 'FF01';
        $numberValue = 'NUMBER_INVOICE';
        $tdocemisor = '01';
        $tdocidentidad = 6;

        if ($result['documento_tdocemisor'] === 'BOLETA') {
            $documento_serie = 'BB01';
            $numberValue = 'NUMBER_VOUCHER';
            $tdocemisor = '03';
            $tdocidentidad = 1;
        }

        if( $documento_numero == '' ){
            $documento_numero = str_pad( ( new ConfiguracionRepo )->GetValor($numberValue)["valor"], 5, '0', STR_PAD_LEFT);
        }

        $values = array(
            'modo' => $config["modo"],
            'emisor_ruc' => $config["emisor_ruc"],
            'emisor_pass' => $config["emisor_pass"],
            'documento_tdocemisor' => $tdocemisor,
            'documento_serie' => $documento_serie,
            'documento_numero' => $documento_numero,
            'documento_moneda' => 'PEN',
            'documento_emision' => date("Y-m-d"),
            'fecha_emision' => date("Y-m-d"),
            'receptor_ruc' => substr($result['empresa_ruc'], 0, 11),
            'receptor_tdocidentidad' => $tdocidentidad,
            'receptor_razonsocial' => $result['empresa_nombre'],
            'propiedad_adicional' => [
                ['id' => '1000', 'nombre' => 'Monto en Letras', 'valor' => ( new SessionRepo )->numeroLetras($totalMontoPagar)],
            ],
            'totalventa' => $totalMontoPagar,
            'totalmonetario' => [
                array('id' => '1001', 'montopagable' => $totalventa), //CATALOGO 15
            ],
            'totalimpuesto' => [
                array(
                    'importe' => $importeIgv,
                    'subtotals' => array(
                        array(
                            'id' => '1000',
                            'nombre' => 'IGV',
                            'codigo_impuesto' => 'VAT',
                            'importe' => $importeIgv,
                        )
                    )
                )
            ],
            'comprobante_detalle' => $detalleValues
        );
        // Detracción
        if (isset($result['detraccion']) && $result['detraccion'] > 0.00) {
            array_push($values['propiedad_adicional'], ['id' => '3000', 'valor' => '022']);
            array_push($values['propiedad_adicional'], ['id' => '3001', 'valor' => '00-003-065189']);
            $values['totalmonetario'][] = ['id' => '2003', 'montopagable' => $result['detraccion'], 'porcentaje' => '10'];
        }
        return array( $result, $numberValue, $documento_serie, $documento_numero, $values, $detalle );
    }

    private function nota_config( $result, $config, $documento_numero = '' )
    {
        $data = [];
        $detalleValues = [];
        $totalSinImpuesto = 0;
        $totalMontoPagar  = $result["precio"];
        //Importe
        $totalWarrantyVenta = 0;
        $totalWarrantyMontoPagar = 0;
        $descWarranty = '';

        $totalSinImpuesto = round( $totalMontoPagar / 1.18, 2);
        $importeIgv = $totalMontoPagar - $totalSinImpuesto;

        $detalleValues[0]['cantidad'] = 1;
        $detalleValues[0]['medida'] = 'ZZ';
        $detalleValues[0]['precioconimpuesto'] = $totalMontoPagar;
        $detalleValues[0]['preciosinimpuesto'] = $totalSinImpuesto;
        $detalleValues[0]['preciototal']       = $totalMontoPagar;
        $detalleValues[0]['precioventatipo']   = '01';
        $detalleValues[0]['descripcion']       = 'SERVICIOS EXTRA';
        $detalleValues[0]['customcode']        = 1;
        $detalleValues[0]['tipo_igv']          = '10';
        $detalleValues[0]['igv']               = $importeIgv;

        $numberValue = 'NUMBER_NOTA_DEBITO';
        $documento_serie = 'FF08';
        $documento_tdocemisor = '08';
        if ($result['tipo'] === 'CREDITO') {
            $numberValue = 'NUMBER_NOTA_CREDITO';
            $documento_serie = 'FF07';
            $documento_tdocemisor = '07';
        }

        if( $documento_numero == '' ){
            $documento_numero = str_pad( ( new ConfiguracionRepo )->GetValor($numberValue)["valor"], 5, '0', STR_PAD_LEFT);
        }

        $values = array(
            'modo' => $config["modo"],
            'emisor_ruc' => $config["emisor_ruc"],
            'emisor_pass' => $config["emisor_pass"],
            'documento_tdocemisor' => $documento_tdocemisor,
            'documento_serie' => $documento_serie,
            'documento_numero' => $documento_numero,
            'documento_moneda' => 'PEN',
            'documento_emision' => date("Y-m-d"),
            'fecha_emision' => date("Y-m-d"),
            'receptor_ruc' => substr($result['empresa_ruc'], 0, 11),
            'receptor_tdocidentidad' => 6,
            'receptor_razonsocial' => $result['empresa_nombre'],
            'propiedad_adicional' => [
                ['id' => '1000', 'nombre' => 'Monto en Letras', 'valor' => ( new SessionRepo )->numeroLetras($totalMontoPagar)],
            ],
            'totalventa' => $totalMontoPagar,
            'totalmonetario' => [
                array('id' => '1001', 'montopagable' => $totalSinImpuesto), //CATALOGO 15
            ],
            'totalimpuesto' => [
                array(
                    'importe' => $importeIgv,
                    'subtotals' => array(
                        array(
                            'id' => '1000',
                            'nombre' => 'IGV',
                            'codigo_impuesto' => 'VAT',
                            'importe' => $importeIgv,
                        )
                    )
                )
            ],
            'comprobante_detalle' => $detalleValues,
            'documento_discrepancia' => array(
                'id' => $result['docmod'],
                'codigo' => '0' . $result['cod_discrepancia'],
                'descripcion' => $result['observacion'],
            ), //CATALOGO 09
            'documento_billing' => array('id' => $result['docmod'], 'tipo_documento' => '01',)
        );

        return array( $result, $numberValue, $documento_serie, $documento_numero, $values, $detalleValues );//$detalle
    }
}
?>