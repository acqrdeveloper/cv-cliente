@extends('mail.base')
@section('content')
<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;" class="email-container">
    <!-- 1 Column Text + Button : BEGIN -->
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
                        <p style="margin: 0;">Súmate al Éxito le da la bienvenida y agradece su preferencia. Para su comodidad y mayor control de sus servicios procedemos a enviarle sus accesos.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 40px 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555; text-align: center;">
                        <p style="margin: 0;"><b>Usuario:</b> {{ $username }}</p>
                        <p style="margin: 0;"><b>Contraseña:</b> {{ $password }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 40px 20px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
                        <!-- Button : BEGIN -->
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: auto;">
                            <tr>
                                <td style="border-radius: 3px; background: #222222; text-align: center;" class="button-td">
                                    <a href="{{ env('APP_URL') }}" style="background: #222222; border: 15px solid #222222; font-family: sans-serif; font-size: 13px; line-height: 1.1; text-align: center; text-decoration: none; display: block; border-radius: 3px; font-weight: bold;" class="button-a">
                                        <span style="color:#ffffff;" class="button-link">&nbsp;&nbsp;&nbsp;&nbsp;Ir al Panel&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <!-- Button : END -->
                    </td>
                </tr>

            </table>
        </td>
    </tr>
    <!-- 1 Column Text + Button : END -->

    <!-- 1 Column Text : BEGIN -->
    <tr>
        <td bgcolor="#ffffff">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td style="padding: 40px 40px 30px 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;text-align: justify">
                        <p style="margin: 0 0 20px 0;">Ingrese a este link <a href="http://account.centrosvirtuales.com/docs/manual.pdf" target="_blank">Manual de Usuario</a> para que encuentre el manual de uso de nuestro Panel.</p>
                        <p style="margin: 0 0 10px 0; font-size: 12px !important;">
                        	Importante: descargue la aplición de Súmate al Éxito que le permitirá tener el control del servicio en la palma de su mano. Encuentrenos desde tu Android (Play Store) o Iphone (App Store) como <b>Súmate al Éxito</b>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- 1 Column Text : END -->

</table>
@endsection