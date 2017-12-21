@extends('mail.base')
@section('content')
<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;" class="email-container">
    <tr>
        <td bgcolor="#ffffff">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 40px 40px 30px; text-align: center;">
                        <h1 style="margin: 0; font-family: sans-serif; font-size: 24px; line-height: 27px; color: #333333; font-weight: normal;">Sr(a). {{ $fullname }}</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 40px 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555; text-align: justify;">
                        <p style="margin: 0;">
						@if($estado=='A')
                        Súmate al Éxito, mediante este mensaje confirma tu reserva realizada para el día <b>{{ $fecha_reserva }}</b>.
                        @elseif($estado == 'C')
                        Gracias por confiar en nosotros, su reserva para el día {{ $fecha_reserva }} será revisado por nuestras asesoras y se comunicarán con usted en la brevedad.
                        @elseif($estado == 'P')
                        La reserva para el día {{ $fecha_reserva }} ha sido registrado. Cuando complete el pago se notificará por este medio la conformidad de la misma. Recuerde que tiene como máximo {{ $horas }} hora(s) para completar el pago.
                        @elseif($estado == 'E')
                        La reserva para el día {{ $fecha_reserva }} ha sido cancelada.
                        @endif
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 40px 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555; text-align: center;">
						<table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td style="font-weight: bold;text-align: right;">Sede</td>
								<td style="padding-left: 10px;text-align: left;">{{ $local_nombre }}</td>
							</tr>
							<tr>
								<td style="font-weight: bold;text-align: right;">Oficina</td>
								<td style="padding-left: 10px;text-align: left;">{{ $espacio }}</td>
							</tr>
							<tr>
								<td style="font-weight: bold;text-align: right;">Horario</td>
								<td style="padding-left: 10px;text-align: left;">De {{ $hora_inicio }} a {{ $hora_fin }}</td>
							</tr>
							<tr>
								<td style="font-weight: bold;text-align: right;">Cochera</td>
								<td style="padding-left: 10px;text-align: left;">{{ $cochera }}</td>
							</tr>
						</table><br>
                    </td>
                </tr>
                @if($modelo_id != '1' && $estado != 'E')
                <tr>
                    <td style="padding: 0 40px 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
                        <div style="border: 1px solid #f1f1f1;padding: 10px;"><b>Resumen de Pago</b></div>
                        <table cellspacing="0" cellpadding="0" border="0" style="width:100%">                            
                            <tr style="border-bottom: 1px solid #f1f1f1;">
                                <th style="font-weight: bold;text-align: left;">Concepto de Pago</th>
                                <th style="font-weight: bold;text-align: right;">Cantidad</th>
                                <th style="font-weight: bold;text-align: right;">Precio Unitario (S/.)</th>
                                <th style="font-weight: bold;text-align: right;">Precio Total (S/.)</th>
                            </tr>
                            @foreach($detalle as $det)
                            <tr style="border-bottom: 1px solid #f1f1f1;">
                                <td style="text-align: left;">{{ $det['concepto'] }}</td>
                                <td style="text-align: right;">{{ $det['cantidad'] }}</td>
                                <td style="text-align: right;">{{ number_format($det['preciou'],2) }}</td>
                                <td style="text-align: right;">{{ number_format($det['preciot'],2) }}</td>
                            </tr>
                            @endforeach
                            <tr style="border-bottom: 1px solid #f1f1f1;">
                                <td style="font-weight: bold;text-align: right;" colspan="3">Precio Total</td>
                                <td style="text-align: right;">{{ number_format($total,2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                 @endif
                @if($estado == 'A')
                <tr>
                    <td style="padding: 0 40px 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555; text-align: justify;">
                        <p style="margin: 0; font-size: 12px">Nota: Si te faltó tiempo para tus reuniones, solicita horas extras a un costo de 30 nuevos soles la hora y has el pago en tu próximo ciclo de facturación.</p>
                    </td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 0 40px 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555; text-align: justify;">
                        <p style="margin: 0;">Saludos cordiales!<br><strong>Súmate al Éxito.</strong></p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection