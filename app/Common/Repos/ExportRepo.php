<?php
namespace CVClient\Common\Repos;
ini_set('max_execution_time', 3000);
use PHPExcel;
use PHPExcel_IOFactory;
use CVClient\Common\Repos\QueryRepo;

class ExportRepo
{

    public function __construct()
    {
        global $aligncenter;
        global $alignright;
        global $alignleft;
        $this->aligncenter = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $this->alignright = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        );
        $this->alignleft = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            )
        );
        $this->verticalcenter = \PHPExcel_Style_Alignment::VERTICAL_CENTER;

        $this->redfont =  array(
            'font' => array(
                'bold'  => true,
                'color' => array('rgb' => 'e47b7b')
            )
        );

        $this->backgroundred =  array(
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'e47b7b')
            )
        );
        $this->backgroundredsuspendedido =  array(
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'eca41f')
            )
        );
        $this->backgroundredpendiente =  array(
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'd0d014')
            )
        );
    }


    public function Data_cliente_reporte_cdr($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $fila = 1;
        $col = -1;
        $head = array('Fecha', 'Origen', 'Destino', 'Tiempo', 'Estado');
        $bodysql = array('calldate', 'src', 'dst', 'tiempo', 'disposition');


        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_cliente_reporte_cdr($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            $sheet->getStyle('A' . $fila . ':E' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(8);
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }


    public function Data_dashboard_empresasregistradas($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $data = ( new QueryRepo )->Q_dashboard_empresasregistradas( $params );
        $usuarioarray = [];
        $usuariolista = [];
        $usuarioacumu = [];
        $planarray    = [];
        $planlista    = [];
        $planacumu    = [];
        $planes       = [];

        foreach( $data["rows"] as $r ){
            $r = (array)$r;

            if( !isset( $usuarioacumu[$r["asesor"]] ) ){
                $usuarioacumu[$r["asesor"]] = 0;
                array_push( $usuariolista, $r["asesor"] );
            }
            $usuarioacumu[$r["asesor"]] = $usuarioacumu[$r["asesor"]] + 1;

            if( !isset( $planacumu[$r["plan_id"]] ) ){
                $planacumu[$r["plan_id"]] = 0;
                array_push( $planlista, $r["plan_id"] );
                $planes[$r["plan_id"]] = $r["plan"];

            }
            $planacumu[$r["plan_id"]] = $planacumu[$r["plan_id"]] + 1;
        }

        foreach( $planlista as $l ){
            array_push( $planarray,[ "id" => $planes[$l], "value" => $planacumu[$l] ] );
        }

        foreach( $usuariolista as $l ){
            array_push( $usuarioarray,[ "id" => $l, "value" => $usuarioacumu[$l] ] );
        }

        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Resumen');
        $fila = 2;
        $col  = -1;

        $body = array( "id", "value" );
        $head1 = array( "Plan", "Cantidad" );
        $head2 = array( "Usuario", "Cantidad" );

        foreach ($head1 as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        foreach( $planarray as $r ){
            $fila = $fila + 1;
            $col = -1;
            foreach ($body as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
        }
        $fila = $fila + 1;
        $col  = 1;
        $sheet->setCellValue( 'B'.$fila, '=SUM(B3:B'.($fila-1).')' );

        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('B'.$fila)->getFont()->setBold(true);
        $sheet->getStyle('B'.$fila)->getFont()->setSize(10);
        $sheet->getStyle('B2:B' . $fila)->applyFromArray($this->alignright);
        $sheet->getStyle('B2:B' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $fila = 2;
        $col  = 2;
        foreach ($head2 as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        foreach( $usuarioarray as $r ){
            $fila = $fila + 1;
            $col = 2;
            foreach ($body as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
        }
        $fila = $fila + 1;
        $col  = 4;
        $sheet->setCellValue( 'E'.$fila, '=SUM(E3:E'.($fila-1).')' );

        $sheet->getStyle('D2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('E'.$fila)->getFont()->setBold(true);
        $sheet->getStyle('E'.$fila)->getFont()->setSize(10);
        $sheet->getStyle('E2:E' . $fila)->applyFromArray($this->alignright);
        $sheet->getStyle('E2:E' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('D2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A2:E2')->getFont()->setSize(10);
        $sheet->getStyle('A2:E2')->getFont()->setBold(true);

        $sheet = $this->autoSize($sheet, $alpha, $alpha);

        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Detalle');
        $fila = 1;
        $col = -1;

        $head = array( "Asesor", "Empresa", "Plan", "Ciclo", "Creacion", "Contrato"  );
        $bodysql = array( "asesor", "empresa_nombre", "plan", "preferencia_facturacion", "fecha_creacion", "contrato"  );

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            if( $r["preferencia_estado"] == 'E' ){
                $sheet->getStyle('B'. $fila)->applyFromArray( $this->backgroundred );
            }else if( $r["preferencia_estado"] == 'S' ){
                $sheet->getStyle('B'. $fila)->applyFromArray( $this->backgroundredsuspendedido );
            }else if( $r["preferencia_estado"] == 'P' ){
                $sheet->getStyle('B'. $fila)->applyFromArray( $this->backgroundredpendiente );
            }
        }

        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('C2:' .'F' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->setAutoFilter('A1:'.$alpha[$col] .'1');
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_dashboard_empresahistorial($objPHPExcel, $params)
    {

        $alpha = $this->alpha();
        $data = ( new QueryRepo )->Q_dashboard_empresahistorial( $params );

        $estadoarray = [];
        $estadolista = [];
        $estadoacumu = [];
        $estados     = [];
        $estados["A"] = "Activa";
        $estados["S"] = "Suspendida";
        $estados["E"] = "Eliminado";
        $estados["P"] = "Pendiente";

        foreach( $data["rows"] as $r ){
            $r = (array)$r;
            if( !isset( $estadoacumu[$r["preferencia_estado"]] ) ){
                $estadoacumu[$r["preferencia_estado"]] = 0;
                array_push( $estadolista, $r["preferencia_estado"] );
                $planes[$r["preferencia_estado"]] = $r["preferencia_estado"];

            }
            $estadoacumu[$r["preferencia_estado"]] = $estadoacumu[$r["preferencia_estado"]] + 1;
        }
        foreach( $estadolista as $l ){
            array_push( $estadoarray,[ "id" => $estados[$l], "value" => $estadoacumu[$l] ] );
        }

        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Resumen');
        $fila = 2;
        $col  = -1;

        $body = array( "id", "value" );
        $head = array( "Estado", "Cantidad" );

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        foreach( $estadoarray as $r ){
            $fila = $fila + 1;
            $col = -1;
            foreach ($body as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
        }
        $fila = $fila + 1;
        $col  = 1;
        $sheet->setCellValue( 'B'.$fila, '=SUM(B3:B'.($fila-1).')' );

        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('B'.$fila)->getFont()->setBold(true);
        $sheet->getStyle('B'.$fila)->getFont()->setSize(10);
        $sheet->getStyle('B2:B' . $fila)->applyFromArray($this->alignright);
        $sheet->getStyle('B2:B' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A2:B2')->getFont()->setSize(10);
        $sheet->getStyle('A2:B2')->getFont()->setBold(true);

        $sheet = $this->autoSize($sheet, $alpha, $alpha);

        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Detalle');
        $fila = 1;
        $col = -1;

        $head = array( "Asesor", "Empresa", "Fecha", "Contrato", "Empleado"  );
        $bodysql = array( "asesor", "empresa_nombre", "fecha", "contrato", "empleado"  );

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            if( $r["preferencia_estado"] == 'E' ){
                $sheet->getStyle('B'. $fila)->applyFromArray( $this->backgroundred );
            }else if( $r["preferencia_estado"] == 'S' ){
                $sheet->getStyle('B'. $fila)->applyFromArray( $this->backgroundredsuspendedido );
            }else if( $r["preferencia_estado"] == 'P' ){
                $sheet->getStyle('B'. $fila)->applyFromArray( $this->backgroundredpendiente );
            }
        }

        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('C2:' .'D' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->setAutoFilter('A1:'.$alpha[$col] .'1');
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_payment($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Pagos');
        $fila = 1;
        $col = -1;
        $head = array( 'Empresa', 'Comprobante', 'Numero', 'Creada', 'Pago', 'Tipo de pago', 'Monto', 'Detraccion', 'Monto Visa', 'Interes Visa' );
        $bodysql = array( 'empresa_nombre', 'comprobante', 'numero', 'fecha_emision', 'fecha_pago', 'tipo', 'monto_pago', 'detraccionD', 'dif_dep_pos', 'des_com_pos' );

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_reporte_payment($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }

            if( $r["preferencia_estado"] == 'E' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundred );
            }else if( $r["preferencia_estado"] == 'S' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredsuspendedido );
            }else if( $r["preferencia_estado"] == 'P' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredpendiente );
            }
        }
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('B2:' .'F' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('G2:J' . $fila)->applyFromArray($this->alignright);
        $sheet->getStyle('G2:J' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_invoicepayed($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Comprobantes Pagados');
        $fila = 1;
        $col = -1;
        $head = array( 'Empresa', 'Comprobante', 'Numero', 'Creada', 'Pago', 'Tipo de pago', 'Monto' );
        $bodysql = array( 'empresa_nombre', 'comprobante', 'numero', 'fecha_emision', 'fecha_pago', 'tipo', 'monto_pago' );

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_reporte_invoicepayed($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }

            if( $r["preferencia_estado"] == 'E' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundred );
            }else if( $r["preferencia_estado"] == 'S' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredsuspendedido );
            }else if( $r["preferencia_estado"] == 'P' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredpendiente );
            }
        }
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('B2:' .'F' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('G2:G' . $fila)->applyFromArray($this->alignright);
        $sheet->getStyle('G2:G' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_ownmissing($objPHPExcel, $params)
    {
        
    }


    public function Data_invitado($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Eventos');
        $fila = 1;
        $col = -1;
        $head = array( 'DNI', 'Nom. Ape.', 'Email', 'Movil', 'Asistencia', 'Nuevo'  );
        $bodysql = array( 'dni', 'nomape', 'email', 'movil', 'asistencia', 'nuevo'  );
        
        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_invitado($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
        }

        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('A2:' .'A' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('C2:' .'F' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;  
    }



    public function Data_invitados($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Eventos');
        $fila = 1;
        $col = -1;
        $head = array( 'Evento', 'Fecha', 'Hora inicio', 'Hora fin', 'Sede', 'Invitados', 'Capacidad' );
        $bodysql = array( 'evento_nombre', 'fecha_reserva', 'hora_inicio', 'hora_fin', 'local_nombre', 'invitados', 'capacidad'  );
        
        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_reporte_invitados($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
        }

        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('B2:' .'D' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('F2:' .'G' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;  
    }


    public function Data_monthpay($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Cuadre');
        $fila = 1;
        $col = -1;

        $head = array(
            'Empresa', 'Ciclo', 'Comprobante', 
            'Numero', 'Creada', 'Monto', 
            'Estado', 'Pago', 'Efectivo', 'Deposito', 'BCP', 'CONTINENTAL' 
        );
        $bodysql = array(
            'empresa_nombre', 'preferencia_facturacion', 'comprobante', 
            'numero', 'fecha_emision', 'monto' ,
            'estado', 'fecha_pago', 'efectivo', 'deposito', 'bcp', 'continental' 
        );

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_reporte_monthpay($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }

            if( $r["preferencia_estado"] == 'E' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundred );
            }else if( $r["preferencia_estado"] == 'S' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredsuspendedido );
            }else if( $r["preferencia_estado"] == 'P' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredpendiente );
            }

            if( $r["estado"] == 'ANULADA' ){
                $sheet->getStyle('G'. $fila)->applyFromArray( $this->redfont );
            }

        }
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('B2:' .'E' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('G2:' .'H' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('F2:F' . $fila)->applyFromArray($this->alignright);
        $sheet->getStyle('F2:F' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('I2:L' . $fila)->applyFromArray($this->alignright);
        $sheet->getStyle('I2:L' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;  
    }

    public function Data_guarantee($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Garantias');
        $fila = 1;
        $col = -1;
        $head = array( 'Empresa', 'Número', 'Observacion', 'Fecha de Uso', 'Monto', 'Se Uso en' );
        $bodysql = array( 'empresa_nombre', 'numero', 'descripcion', 'fecha_uso', 'monto_pago', 'nfacturaUso' );

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_reporte_guarantee($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }

            if( $r["preferencia_estado"] == 'E' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundred );
            }else if( $r["preferencia_estado"] == 'S' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredsuspendedido );
            }else if( $r["preferencia_estado"] == 'P' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredpendiente );
            }
        }
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('B2:' .'D' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('F2:' .'F' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('E2:E' . $fila)->applyFromArray($this->alignright);
        $sheet->getStyle('E2:E' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('A2:' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;        
    }

    public function Data_facturanualreporte($objPHPExcel, $params)
    {
        $data = (new QueryRepo)->Q_facturanualreporte($params);
        $mestota = [];
        $meslist = [];
        $mesvali = [];
        $sheetmes = [];
        foreach( $data["rows"] as $d ){
            $d = (array)$d;
            $mes = date_format( date_create( $d["emision"] ), "m" );
            if( !isset( $mesvali[$mes] ) ){
                array_push( $meslist, $mes );
                $mesvali[$mes] = 1;
                $sheetmes[$mes] = [];
                $mestota[$mes] = [];
                $mestota[$mes]["TOTAL"] = 0;
            }
            if( !isset( $mestota[$mes][$d["comprobante"]] ) ){
                $mestota[$mes][$d["comprobante"]] = [];
                $mestota[$mes][$d["comprobante"]]["total"] = 0;
                $mestota[$mes][$d["comprobante"]]["count"] = 0;
                $mestota[$mes][$d["comprobante"]]["pagado"] = 0;
            }

            if( $d["comprobante"] != "CREDITO" && $d["comprobante"] != "DEBITO" ){
                $mestota[$mes]["TOTAL"] = $mestota[$mes]["TOTAL"] + $d["monto"];
            }

            if( $d["estado"] == "PAGADA" ){
                $mestota[$mes][$d["comprobante"]]["pagado"] = $mestota[$mes][$d["comprobante"]]["pagado"] + $d["monto"];
            }
            $mestota[$mes][$d["comprobante"]]["total"] = $mestota[$mes][$d["comprobante"]]["total"] + $d["monto"];

            $mestota[$mes][$d["comprobante"]]["count"] = $mestota[$mes][$d["comprobante"]]["count"] + 1;

            array_push( $sheetmes[$mes], $d );
        }
        unset( $data );

        $alpha = $this->alpha();
        $monthID = $this->monthNameById();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('RESUMEN');

        $fila = 1;
        $col  = 0;

        $head = array( 'mes', 'factura', 'boleta', 'PROVICIONAL', 'credito', 'debito', 'total' );
        foreach ($head as $key => $h) {
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
            $col = $col + ( $key > 0 ? ($key > 3 ? 2 : 3) : 1);
        }

        $fila = 2;
        $col  = 1;
        foreach ($head as $key => $h) {
            if( $key > 0 && $key <= 5 ){
                $coordinate = $alpha[$col] . $fila;
                $sheet->setCellValue($coordinate, strtoupper("contador"));
                $coordinate = $alpha[$col+1] . $fila;
                $sheet->setCellValue($coordinate, strtoupper("total"));
                $coordinate = $alpha[$col+2] . $fila;
                $sheet->setCellValue($coordinate, strtoupper("pagado"));
                if( $key > 0 && $key <= 3 ){
                    $col = $col + 3;
                }else{
                    $col = $col + 2;
                }
            }
        }
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:D1');
        $sheet->mergeCells('E1:G1');
        $sheet->mergeCells('H1:J1');
        $sheet->mergeCells('K1:L1');
        $sheet->mergeCells('M1:N1');
        $sheet->mergeCells('O1:O2');

        $sheet->getStyle('A1:O1')->getFont()->setSize(10)->setBold(true);
        $sheet->getStyle('A2:O2')->getFont()->setSize(9)->setBold(true);
        $sheet->getStyle('A1:O2')->applyFromArray($this->aligncenter);
        $sheet->getStyle('A1:O2')->getAlignment()->setVertical($this->verticalcenter);

        foreach( $meslist as $m ){
            $fila = $fila + 1;
            $col  = 0;
            foreach ($head as $key => $h) {
                if( $key > 0 && $key <= 5 ){
                    $coordinate = $alpha[$col] . $fila;
                    $val = isset( $mestota[$m][strtoupper( $h )]["count"] ) ? $mestota[$m][strtoupper( $h )]["count"] : 0;
                    $sheet->setCellValue( $coordinate, $val );
                    $col = $col + 1;
                    $coordinate = $alpha[$col] . $fila;
                    $val = isset( $mestota[$m][strtoupper( $h )]["total"] ) ? $mestota[$m][strtoupper( $h )]["total"] : 0;
                    $sheet->setCellValue( $coordinate, $val );
                    if( $key <= 3 ){
                        $col = $col + 1;
                        $coordinate = $alpha[$col] . $fila;
                        $val = isset( $mestota[$m][strtoupper( $h )]["pagado"] ) ? $mestota[$m][strtoupper( $h )]["pagado"] : 0;
                        $sheet->setCellValue( $coordinate, $val );/*SUMAR.SI.CONJUNTO*/
                    }
                }else{
                    $coordinate = $alpha[$col] . $fila;
                    if( $key <= 0 ){
                        $sheet->setCellValue( $coordinate, $monthID[$m] );
                    }else{
                        $sheet->setCellValue( $coordinate, $mestota[$m]["TOTAL"] );
                    }
                }
                $col = $col + 1;
            }
        }

        $sheet->getStyle('A3:' . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('A3:' . $alpha[$col] . ($fila+1))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B3:' . $alpha[$col] . ($fila+1))->applyFromArray($this->aligncenter);

        $sheet->getStyle('C3:C' . ($fila+1))->applyFromArray($this->alignright);
        $sheet->getStyle('D3:D' . ($fila+1))->applyFromArray($this->alignright);
        $sheet->getStyle('F3:F' . ($fila+1))->applyFromArray($this->alignright);
        $sheet->getStyle('G3:G' . ($fila+1))->applyFromArray($this->alignright);
        $sheet->getStyle('I3:I' . ($fila+1))->applyFromArray($this->alignright);
        $sheet->getStyle('J3:J' . ($fila+1))->applyFromArray($this->alignright);
        $sheet->getStyle('L3:L' . ($fila+1))->applyFromArray($this->alignright);
        $sheet->getStyle('N3:N' . ($fila+1))->applyFromArray($this->alignright);
        $sheet->getStyle('O3:O' . ($fila+1))->applyFromArray($this->alignright);

        $sheet->getStyle('C3:C' . ($fila+1))->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('D3:D' . ($fila+1))->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('F3:F' . ($fila+1))->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('G3:G' . ($fila+1))->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('I3:I' . ($fila+1))->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('J3:J' . ($fila+1))->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('L3:L' . ($fila+1))->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('N3:N' . ($fila+1))->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('O3:O' . ($fila+1))->getNumberFormat()->setFormatCode("#,##0.00");

        $fila = $fila + 1;

        $sheet->setCellValue( 'B'.$fila, '=SUM(B3:B'.($fila-1).')' );
        $sheet->setCellValue( 'C'.$fila, '=SUM(C3:C'.($fila-1).')' );
        $sheet->setCellValue( 'D'.$fila, '=SUM(D3:D'.($fila-1).')' );
        $sheet->setCellValue( 'E'.$fila, '=SUM(E3:E'.($fila-1).')' );
        $sheet->setCellValue( 'F'.$fila, '=SUM(F3:F'.($fila-1).')' );
        $sheet->setCellValue( 'G'.$fila, '=SUM(G3:G'.($fila-1).')' );
        $sheet->setCellValue( 'H'.$fila, '=SUM(H3:H'.($fila-1).')' );
        $sheet->setCellValue( 'I'.$fila, '=SUM(I3:I'.($fila-1).')' );
        $sheet->setCellValue( 'J'.$fila, '=SUM(J3:J'.($fila-1).')' );
        $sheet->setCellValue( 'K'.$fila, '=SUM(K3:K'.($fila-1).')' );
        $sheet->setCellValue( 'L'.$fila, '=SUM(L3:L'.($fila-1).')' );
        $sheet->setCellValue( 'M'.$fila, '=SUM(M3:M'.($fila-1).')' );
        $sheet->setCellValue( 'N'.$fila, '=SUM(N3:N'.($fila-1).')' );
        $sheet->setCellValue( 'O'.$fila, '=SUM(O3:O'.($fila-1).')' );
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(9)->setBold(true);
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        unset( $mestota );

        $head = array( 'emision', 'comprobante', 'numero', 'monto', 'ruc', 'empresa', 'plan', 'ciclo', 'estado', 'pago' );
        foreach( $meslist as $key => $m ){
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex($key+1);
            $sheet = $objPHPExcel->getActiveSheet();
            $sheet->setTitle( strtoupper( $monthID[$m] ) );
            
            $fila = 1;
            $col  = 0;

            foreach ($head as $key => $h) {
                $coordinate = $alpha[$col] . $fila;
                $sheet->setCellValue( $coordinate, strtoupper( $h ) );
                $col = $col + 1;
            }
            $sheet->setAutoFilter( 'A'.$fila.':'.$alpha[$col].$fila );
            $sheet->getStyle('A'.$fila.':'.$alpha[$col].$fila)->getFont()->setSize(10)->setBold(true);
            $sheet->getStyle('A'.$fila.':'.$alpha[$col].$fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('A'.$fila.':'.$alpha[$col].$fila)->getAlignment()->setVertical($this->verticalcenter);

            foreach ( $sheetmes[$m] as $r) {
                $r = (array)$r;
                $fila = $fila + 1;
                $col = -1;
                foreach ($head as $b) {
                    $col = $col + 1;
                    $coordinate = $alpha[$col] . $fila;
                    $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
                }
                
                if( $r["preferencia_estado"] != 'A' ){
                    $sheet->getStyle('E' . $fila . ':F'. $fila)->applyFromArray( $this->backgroundred );
                }
            }

            $sheet->getStyle('A2:' .$alpha[$col].$fila )->getFont()->setSize(8);
            $sheet->getStyle('A2:' .$alpha[$col].($fila+1))->getAlignment()->setVertical($this->verticalcenter);
            $sheet->getStyle('A2:' .$alpha[$col].$fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('D2:D' . ($fila+1))->applyFromArray($this->alignright);
            $sheet->getStyle('F2:F' . $fila)->applyFromArray($this->alignleft);
            $sheet->getStyle('D2:D' . ($fila+1))->getNumberFormat()->setFormatCode("#,##0.00");

            $fila = $fila + 1;
            $col = 1;
            $sheet->setCellValue( 'D'.$fila, '=SUM(D2:D'.($fila-1).')' );
            $sheet->getStyle('D'.$fila)->getFont()->setSize(9);            
            $sheet = $this->autoSize($sheet, $alpha, $alpha);
        }

        return $objPHPExcel;
    }

    public function Data_recado($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $fila = 1;
        $col = -1;
        $head = array(
            'empresa', 'fecha de ingreso', 'para',
            'contenido', 'creado por', 'entregado a',
            'fecha de entrega', 'entregado por', 'lugar'
        );
        $bodysql = array(
            'empresa_nombre', 'fecha_creacion', 'para',
            'contenido_paquete', 'creado_por', 'entregado_a',
            'fecha_entrega', 'entregado_por', 'lugar'
        );

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $data = (new QueryRepo)->Q_recado($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            $sheet->getStyle('B' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('G' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(8);
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_pagos($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('PAGOS');
        $fila = 1;
        $col = -1;
        $head = array(
            'empresa', 'comprobante', 'numero', 'creado',
            'pagado', 'tipo de paga', 'por', 'factura',
            'monto', 'detraccion', 'comision');
        $bodysql = array(
            'empresa_nombre', 'comprobante', 'numero', 'fecha_creacion',
            'fecha_pago', 'tipo', 'usuario_pago', 'factura_monto',
            'totalcomision', 'detrac', 'comisionpos');//diferenciapos, a.comisionpos, a.detracDeposito, a.detracEfectivo
        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_pagos($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            $sheet->getStyle('A' . $fila)->applyFromArray($this->alignleft);
            $sheet->getStyle('A' . $fila . ':C' . $fila)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('B' . $fila . ':G' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('H' . $fila . ':K' . $fila)->applyFromArray($this->alignright);
            $sheet->getStyle('H' . $fila . ':K' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(8);
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }

        $sheet = $this->autoSize($sheet, $alpha, $alpha);


        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('VISA');
        $fila = 1;
        $col = -1;

        $head = array(
            'id', 'empresa', 'plan',
            'comprobante', 'numero', 'pagado',
            'total', 'tipo de pago', 'tarjeta',
            'deposito cta', 'comision', 'fecha ingreso',
            'por');

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);


        $bodysql = array(
            'id', 'empresa_nombre', 'nombreplan',
            'comprobante', 'numero', 'fecha_pago',
            'factura_monto', 'tipo', 'nom_pos',
            'diferenciapos', 'comisionpos', 'fechaingreso',
            'usuario_pago');

        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $col = -1;
            if ($r["id_pos"] * 1 >= 1) {////
                $fila = $fila + 1;
                foreach ($bodysql as $b) {
                    $col = $col + 1;
                    $coordinate = $alpha[$col] . $fila;
                    $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
                }

                $sheet->getStyle('A' . $fila)->applyFromArray($this->aligncenter);
                $sheet->getStyle('C' . $fila . ':F' . $fila)->applyFromArray($this->aligncenter);
                $sheet->getStyle('H' . $fila . ':I' . $fila)->applyFromArray($this->aligncenter);
                $sheet->getStyle('L' . $fila)->applyFromArray($this->aligncenter);
                $sheet->getStyle('G' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
                $sheet->getStyle('J' . $fila . ':K' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
                $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(8);
                $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            }
        }
        $sheet = $this->autoSize($sheet, $alpha, $alpha);


        return $objPHPExcel;
    }

    public function Data_facturacion($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('FACTURAS');
        $fila = 1;
        $col = -1;
        $head = array(
            'empresa', 'creado', 'comprobante', 'numero',
            'vencimiento', 'monto', 'nota', 'total',
            'ciclo', 'estado'
        );

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $bodysql = array(
            'empresa_nombre', 'fecha_creacion', 'comprobante', 'numero',
            'fecha_vencimiento', 'monto', 'nota_monto', 'total',
            'preferencia_facturacion', 'estado');

        $data = (new QueryRepo)->Q_facturacion($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
        }
        $sheet = $this->autoSize($sheet, $alpha, $alpha);


        return $objPHPExcel;
    }

    public function Data_correspondencia($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $fila = 1;
        $col = -1;
        $head = array('empresa', 'remitente', 'lugar', 'fecha_entrega', 'entregado a', 'entregado por');
        $bodysql = array('empresa_nombre', 'remitente', 'lugar', 'fecha_entrega', 'entregado_por', 'entregado_a');


        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_correspondencia($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            $sheet->getStyle('C' . $fila . ':D' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(8);
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_correspondenciaempresa($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $fila = 1;
        $col = -1;
        $head = array('ruc', 'empresa', 'eliminado', 'pendiente', 'entregado', 'confirmado', 'total');
        $bodysql = array('empresa_ruc', 'empresa_nombre', 'eliminado', 'pendiente', 'entregado', 'confirmado', 'total');

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_correspondenciaEmpresa($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            $sheet->getStyle('A' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('A' . $fila)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('C' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('D' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->alignright);
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D' . $fila . ':' . $alpha[$col] . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(8);
        }
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_feedback($objPHPExcel, $params)
    {

        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $fila = 1;
        $col = -1;
        $head = array( 'empresa', 'contenido', 'fecha',);
        $bodysql = array('empresa_nombre', 'sugerencia', 'fecha_creacion');

        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_feedback($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            $sheet->getStyle('A' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('A' . $fila)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('C' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('D' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->alignright);
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D' . $fila . ':' . $alpha[$col] . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(8);
        }
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_cdrempresa($objPHPExcel, $params)
    {
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $fila = 1;
        $col = -1;
        $head = array('ruc', 'empresa', 'estado', 'minutos', 'llamadas');
        $bodysql = array('empresa_ruc', 'empresa_nombre', 'preferencia_estado', 'minutos', 'llamadas');
        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $data = (new QueryRepo)->Q_cdrEmpresa($params);
        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            $sheet->getStyle('A' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('A' . $fila)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            $sheet->getStyle('C' . $fila)->applyFromArray($this->aligncenter);
            $sheet->getStyle('D' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->alignright);
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D' . $fila . ':' . $alpha[$col] . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
            $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(8);
        }
        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_empresa($objPHPExcel, $params)
    {
        //Q_empresa
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $fila = 1;
        $col = -1;
        $data = (new QueryRepo)->Q_empresa($params);

        $head = array('Estado', 'Empresa', 'Comercial', 'Ruc', 'Encargado', 'Central', 'Plan', 'Ciclo');
        $bodysql = array('preferencia_estado', 'empresa_nombre', 'nombre_comercial', 'empresa_ruc', 'encargado', 'central', 'plan', 'preferencia_facturacion');
        
        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }

        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }


            if( $r["preferencia_estado"] == 'E' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundred );
            }else if( $r["preferencia_estado"] == 'S' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredsuspendedido );
            }else if( $r["preferencia_estado"] == 'P' ){
                $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredpendiente );
            }
        }

        $sheet->getStyle('A2:A' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('D2:D' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('F2:H' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A2:'  . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('A2:'  . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->setAutoFilter('A1:'.$alpha[$col] .'1');

        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_factura($objPHPExcel, $params){

        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $fila = 1;
        $col = -1;
        $data = (new QueryRepo)->Q_facturacion($params);
        $head = array('Comprobante', 'Numero', 'Emision', 'Vencimiento', 'Monto', 'Igv', 'Total', 'Estado');
        $bodysql = array('comprobante', 'docnum', 'fecha_emision', 'fecha_vencimiento', 'base', 'igv', 'monto', 'estado');
        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            /*
                if( $r["preferencia_estado"] == 'E' ){
                    $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundred );
                }else if( $r["preferencia_estado"] == 'S' ){
                    $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredsuspendedido );
                }else if( $r["preferencia_estado"] == 'P' ){
                    $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredpendiente );
                }
            */
        }

        $sheet->getStyle('A2:D' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('E2:G' . $fila)->applyFromArray($this->alignright);
        $sheet->getStyle('H2:H' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('E2:G' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('A2:'  . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('A2:'  . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->setAutoFilter('A1:'.$alpha[$col] .'1');

        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }

    public function Data_nota($objPHPExcel, $params){
        
        $alpha = $this->alpha();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $fila = 1;
        $col = -1;
        $data = (new QueryRepo)->Q_notas_lista($params);
        $head = array('Comprobante', 'Número', 'Emision', 'Monto', 'Doc. Mod.');
        $bodysql = array('tipo', 'docnum', 'fecha_emision', 'precio', 'docmod_numero');
        
        foreach ($head as $h) {
            $col = $col + 1;
            $coordinate = $alpha[$col] . $fila;
            $sheet->setCellValue($coordinate, strtoupper($h));
        }
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setSize(10);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getFont()->setBold(true);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A' . $fila . ':' . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        foreach ($data["rows"] as $r) {
            $r = (array)$r;
            $fila = $fila + 1;
            $col = -1;
            foreach ($bodysql as $b) {
                $col = $col + 1;
                $coordinate = $alpha[$col] . $fila;
                $r[$b] != "" ? $sheet->setCellValue($coordinate, strtoupper($r[$b])) : "";
            }
            /*
                if( $r["preferencia_estado"] == 'E' ){
                    $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundred );
                }else if( $r["preferencia_estado"] == 'S' ){
                    $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredsuspendedido );
                }else if( $r["preferencia_estado"] == 'P' ){
                    $sheet->getStyle('A'. $fila)->applyFromArray( $this->backgroundredpendiente );
                }
            */
        }

        $sheet->getStyle('A2:C' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('D2:D' . $fila)->applyFromArray($this->alignright);
        $sheet->getStyle('D2:D' . $fila)->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('E2:E' . $fila)->applyFromArray($this->aligncenter);
        $sheet->getStyle('A2:'  . $alpha[$col] . $fila)->getFont()->setSize(8);
        $sheet->getStyle('A2:'  . $alpha[$col] . $fila)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->setAutoFilter('A1:'.$alpha[$col] .'1');

        $sheet = $this->autoSize($sheet, $alpha, $alpha);
        return $objPHPExcel;
    }


    public function createCSV($modulo, $params)
    {
        $filename = $modulo . "-" . date("YmdHis") . ".xlsx";
        $out = $this->exportCreate($params);
        if ($out["load"]) {
            $out["load"] = false;
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("System")
                ->setLastModifiedBy("System")->setTitle("CENTROS_VIRTUALES")->setSubject("CENTROS_VIRTUALES")
                ->setDescription("REPORT GENERATED FROM CENTROS VIRTUALES")->setKeywords("office 2007 openxml php")->setCategory("Test result file");
            $objPHPExcel = call_user_func_array(array($this, "Data_" . $modulo), array($objPHPExcel, $params));
            if ($objPHPExcel) {
                $objPHPExcel->setActiveSheetIndex(0);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                //header('Content-Type: application/openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $filename . '"');
                header('Cache-Control: max-age=0');
                header('Cache-Control: max-age=1');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: cache, must-revalidate');
                header('Pragma: public');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save('php://output');
                $out["load"] = true;
                $out["file"] = $filename;
            }
        }
        return $out;
    }

    private function alpha()
    {
        return array(
            'A', 'B', 'C', 'D', 'E',
            'F', 'G', 'H', 'I', 'J',
            'K', 'L', 'M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE',
            'AF', 'AG', 'AH', 'AI', 'AJ'
        );
    }

    private function monthName()
    {
        return array(
            'Enero', 'Febrero', 'Marzo',
            'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Setiembre',
            'Octubre', 'Noviembre', 'Diciembre'
        );
    }

    private function monthNameById()
    {
        return array(
            '01' => 'Enero', 
            '02' => 'Febrero', 
            '03' => 'Marzo',
            '04' => 'Abril', 
            '05' => 'Mayo', 
            '06' => 'Junio',
            '07' => 'Julio', 
            '08' => 'Agosto', 
            '09' => 'Setiembre',
            '10' => 'Octubre', 
            '11' => 'Noviembre', 
            '12' => 'Diciembre'
        );
    }

    private function month()
    {
        return array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
    }

    public function exportCreate($params)
    {
        $out = array("load" => true, "id" => 0);
        //$out = array( "load" => false, "id" => 0 );
        //$returned = \DB::transaction( function() use( $params, &$out ){
        //$params 		= $this->filterParam( $params ,"export_create" );
        //$seg 			= Export::create( $params );
        //$out["load"] 	= true;
        //$out["id"] 		= $seg->id;
        //});
        return $out;
    }

    private function autoSize($sheet, $alpha, $head)
    {
        $col = 0;
        foreach ($head as $h) {
            $sheet->getColumnDimension($alpha[$col])->setAutoSize(true);
            $col = $col + 1;
        }
        return $sheet;
    }
}

?>