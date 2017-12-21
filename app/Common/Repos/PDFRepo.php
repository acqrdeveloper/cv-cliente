<?php 
namespace CVClient\Common\Repos;
use CVClient\Common\Repos\SessionRepo;
class PDFRepo {

	public function HTML_comprobante( $comprobante, $detalle, $config, $datos, $moddocnum = "", $moddocemi = "", $modmotivo = "" ){
		list( $montoTotal, $montoUnitario, $subTotalVentas, $descuento, $subWarrantyTotal, $warrantyTotal, $descWarranty ) = $datos;

		if( $comprobante["documento_tdocemisor"] == '01' ){
			$comprobante["documento_tdocemisor"] = 'FACTURA';
		}else if( $comprobante["documento_tdocemisor"] == '07' ){
			$comprobante["documento_tdocemisor"] = 'NOTA DE CREDITO';
		}else if( $comprobante["documento_tdocemisor"] == '08' ){
			$comprobante["documento_tdocemisor"] = 'NOTA DE DEBITO';
		}

		$head = '<div>
			        <div style="display: inline-block; width:65%; vertical-align: top;">
			            <div style="line-height:20px; text-align: left; font-weight: bolder;">'.$config["emisor_razonsocial"].'</div>
			            <div style="line-height:20px; text-align: left;">'.$config["emisor_direccion"].'</div>
			            <div style="line-height:20px; text-align: left;">'.strtoupper($config["emisor_distrito"])." - ".strtoupper($config["emisor_provincia"])." - ".strtoupper($config["emisor_departamento"]).'</div>
			        </div>
			        <div style="display: inline-block; width:33%; text-align: right;">
			            <div style="border:1px solid black; font-weight: bolder; text-align: center;">
			                <div style="line-height:20px;">'.$comprobante["documento_tdocemisor"].' ELECTRONICA</div>
			                <div style="line-height:20px;">RUC: '.$config["emisor_ruc"].'</div>
			                <div style="line-height:20px;">'.$comprobante["serie"].'-'.$comprobante["numero"].'</div>
			            </div>
			        </div>
			    </div>';

		$break1 = '<div style="border:0px solid transparent; height: 15px;"></div><div style="border:1px solid black; height: 0px;"></div><div style="border:0px solid transparent; height: 15px;"></div>';

		$comp   = '<div>';
		if( isset($comprobante["fecha_vencimiento"]) && $comprobante["fecha_vencimiento"] != '' ){
			$comp   .= '<div style="line-height: 20px;">
				            <div style="display: inline-block; width:25%; text-align: left;">Fecha de Vencimiento </div>
				            <div style="display: inline-block; width:70%; text-align: left; font-weight: bolder;">: '.$comprobante["fecha_vencimiento"].'</div>
				        </div>';
		}

		$comp   .= '<div style="line-height: 20px;">
			            <div style="display: inline-block; width:25%; text-align: left;">Fecha de Emision     </div>
			            <div style="display: inline-block; width:70%; text-align: left; font-weight: bolder;">: '.$comprobante["fecha_emision"].'</div>
			        </div>
			        <div style="line-height: 20px;">
			            <div style="display: inline-block; width:25%; text-align: left;">Señor(es)            </div>
			            <div style="display: inline-block; width:70%; text-align: left; font-weight: bolder;">: '.$comprobante["empresa_nombre"].'</div>
			        </div>
			        <div style="line-height: 20px;">
			            <div style="display: inline-block; width:25%; text-align: left;">RUC                  </div>
			            <div style="display: inline-block; width:70%; text-align: left; font-weight: bolder;">: '.$comprobante["empresa_ruc"].'</div>
			        </div>
			        <div style="line-height: 20px;">
			            <div style="display: inline-block; width:25%; text-align: left;">Tipo de Moneda       </div>
			            <div style="display: inline-block; width:70%; text-align: left; font-weight: bolder;">: SOLES</div>
			        </div>';
		if( isset($comprobante["docmod"]) && $comprobante["docmod"] != '' ){
			$comp   .= '<div style="line-height: 20px;">
				            <div style="display: inline-block; width:25%; text-align: left;">Doc. que Modifica </div>
				            <div style="display: inline-block; width:70%; text-align: left; font-weight: bolder;">: '.$comprobante["docmod"].'</div>
				        </div>';
		}

		if( isset($comprobante["docmodemision"]) && $comprobante["docmodemision"] != '' ){
			$comp   .= '<div style="line-height: 20px;">
				            <div style="display: inline-block; width:25%; text-align: left;">Doc. se Emitió </div>
				            <div style="display: inline-block; width:70%; text-align: left; font-weight: bolder;">: '.$comprobante["docmodemision"].'</div>
				        </div>';
		}

		$comp   .= '<div style="line-height: 20px;">
			            <div style="display: inline-block; width:25%; text-align: left;">Observación          </div>
			            <div style="display: inline-block; width:70%; text-align: left; font-weight: bolder;">: '.(isset($comprobante["observacion"]) ? $comprobante["observacion"] : '' ).'</div>
			        </div>';
			        
		$comp   .= '</div>';

		$break2 = '<div style="border:0px solid transparent; height: 15px;"></div>';
		$deta   =    '<div>
				        <table style="width: 100%; border-collapse: collapse; border:1px solid black; ">
				            <thead>
				                <tr style="font-weight: bolder; font-size: 13px;">
				                    <td style="padding:3px; border-top:1px solid black; text-align: center;">Cantidad</td>
				                    <td style="padding:3px; border-top:1px solid black; ">Medida</td>
				                    <td style="padding:3px; border-top:1px solid black; ">Descripción</td>
				                    <td style="padding:3px; border-top:1px solid black; text-align: center;">Valor Unitario</td>
				                </tr>
				            </thead>
				            <tbody>';

		if ($subTotalVentas > 0) {
			$deta   .=  '<tr>
		                    <td style="padding:3px; border-top:1px solid black; text-align: center;">1 </td>
		                    <td style="padding:3px; border-top:1px solid black; text-align: left;">UNIDAD </td>
		                    <td style="padding:3px; border-top:1px solid black; text-align: left;">'.(isset($detalle[0]['descripcion_sunat']) ? $detalle[0]['descripcion_sunat'] : 'SERVICIO EN OFICINAS VIRTUALES').'</td>
		                    <td style="padding:3px; border-top:1px solid black; text-align: right;">S/. ' . number_format($subTotalVentas, 2).'</td>
		                </tr>';
		}

		if ($subWarrantyTotal > 0) {
			$deta   .=  '<tr>
		                    <td style="padding:3px; border-top:1px solid black; text-align: center;">1 </td>
		                    <td style="padding:3px; border-top:1px solid black; text-align: left;">UNIDAD </td>
		                    <td style="padding:3px; border-top:1px solid black; text-align: left;">'.(isset($descWarranty) ? $descWarranty : 'GARANTIA').'</td>
		                    <td style="padding:3px; border-top:1px solid black; text-align: right;">S/. ' . number_format($subWarrantyTotal, 2).'</td>
		                </tr>';

			$subTotalVentas += $subWarrantyTotal;
			$montoTotal += $warrantyTotal;
		}

		$montoUnitario = round($montoTotal / 1.18, 2);
		$deta   .=          '</tbody></table></div>';
		$break3 = '<div style="border:0px solid transparent; height: 10px;"></div>';
		$totale =  '<div>
				        <table style="width: 100%;">
				            <thead><tr><td></td><td</td><td></td><td></td><td</td><td></td></tr></thead>
				            <tbody>
				                <tr>
				                    <td colspan="3"></td>
				                    <td style="text-align: right;">Sub Total Ventas</td>
				                    <td style="text-align: center;">:</td>
				                    <td style="text-align: right; border: 1px solid black;">S/. '.number_format($subTotalVentas, 2).'</td>
				                </tr>
				                <tr>
				                    <td colspan="3"></td>
				                    <td style="text-align: right;">Anticipos</td>
				                    <td style="text-align: center;">:</td>
				                    <td style="text-align: right; border: 1px solid black;">S/. 0.00</td>
				                </tr>
				                <tr>
				                    <td style="text-align: right;">Valor de Venta de Operaciones Gratuitas </td>
				                    <td style="text-align: center;">: </td>
				                    <td style="text-align: right; border: 1px solid black;">S/. 0.00</td>
				                    <td style="text-align: right;">Descuentos</td>
				                    <td style="text-align: center;">:</td>
				                    <td style="text-align: right; border: 1px solid black;">S/. 0.00</td>
				                </tr>
				                <tr>
				                    <td colspan="3"></td>
				                    <td style="text-align: right;">Valor de Venta</td>
				                    <td style="text-align: center;">:</td>
				                    <td style="text-align: right; border: 1px solid black;">S/. '.number_format($montoUnitario, 2).'</td>
				                </tr>
				                <tr>
				                    <td colspan="3" style="text-align: left; font-weight: bolder;">SON: '.( new SessionRepo )->numeroLetras($montoTotal).'</td>
				                    <td style="text-align: right;">ISC</td>
				                    <td style="text-align: center;">:</td>
				                    <td style="text-align: right; border: 1px solid black;">S/. 0.00</td>
				                </tr>
				                <tr>
				                    <td colspan="3"></td>
				                    <td style="text-align: right;">IGV</td>
				                    <td style="text-align: center;">:</td>
				                    <td style="text-align: right; border: 1px solid black;">S/. '.number_format($montoTotal - $montoUnitario, 2).'</td>
				                </tr>
				                <tr>
				                    <td colspan="3"></td>
				                    <td style="text-align: right;">Otros Cargos</td>
				                    <td style="text-align: center;">:</td>
				                    <td style="text-align: right; border: 1px solid black;">S/. 0.00</td>
				                </tr>
				                <tr>
				                    <td colspan="3"></td>
				                    <td style="text-align: right;">Otros Tributos</td>
				                    <td style="text-align: center;">:</td>
				                    <td style="text-align: right; border: 1px solid black;">S/. 0.00</td>
				                </tr>
				                <tr>
				                    <td colspan="3"></td>
				                    <td style="text-align: right; font-weight:bolder; ">Importe Total</td>
				                    <td style="text-align: center;">:</td>
				                    <td style="text-align: right; border: 1px solid black; font-weight:bolder; font-size:102%;">S/. '.number_format($montoTotal, 2).'</td>
				                </tr>
				            </tbody>
				        </table>
				    </div>';
		$break4 = '<div style="border:0px solid transparent; height: 20px;"></div><div style="border:1px solid black; text-align:center; padding:5px;"> Esta es una representación impresa de la factura electrónica, generada en el Sistema de SUNAT. Puede verificarla utilizando su clave SOL.</div>';

		$html = '<div style=" font-family: monospace; font-size: 12px;">'.$head.$break1.$comp.$break2.$deta.$break3.$totale.$break4.'</div>';

		return $html;
	}

	public function PDF_HTML( $html ){
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        return $pdf;
	}
}
