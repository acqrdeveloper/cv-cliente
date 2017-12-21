<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
  	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Registro | Súmate al Éxito</title>
	<link rel="stylesheet" href="{{ asset('/appdev/registro/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('/appdev/registro/fontawesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('/appdev/registro/theme.css') }}">
	<style type="text/css">
		#services {
			background-image: /*linear-gradient(to top, #a9a9a9 0%, #989797 100%),*/ url('{{ asset('/images/bg.jpg') }}');
			background-position: top center;
			background-repeat: no-repeat;
			background-size: 100% auto;
	    	/*background-blend-mode: multiply;*/
		}

		.btn-primary2 {
			background-color: #354f88;
			border-color: #354f88;
		}

		.text-warning {
			color: #ff9c00;
		}

		footer {
    		padding: 28px 0;
		}
	</style>
</head>
<body id="page-top" class="index">
	<section id="services">
		<div class="mt40 hidden-xs"></div>
		<div class="container">
			<div class="row mb50">
				<div class="col-md-6 hidden-xs">
					<img src="{{ asset('/images/girl.png') }}" width="100%;">
				</div>
				<div class="col-md-6" id="first-step">
					<h3 class="text-center"><strong>!TU OFICINA POR <span class="text-warning">7 DIAS GRATIS¡</span></strong></h3>
					<div class="text-center">Arma tu oficina en tan solo <span class="text-primary">3 minutos</span> y comienza a emprender de forma inmediata</div>
					<div class="mt10 visible-xs">&nbsp;</div>
					<div><div id="alert-message"></div></div>
					<div class="mt30 ml15 mr15" id="social-container">
						<button class="btn btn-primary btn-primary2 btn-block mb10" id="btn-fb-register" data-auth="facebook"><i class="fa fa-facebook mr10"></i> Comienza con Facebook</button>
						<button class="btn btn-danger btn-block mb10" id="btn-goo-register" data-auth="google"><i class="fa fa-google-plus mr10"></i> Comienza con Gmail</button>
						<button class="btn btn-default btn-block mb10" id="btn-mail-register" data-auth="email"><i class="fa fa-envelope-o mr10"></i> Comienza con Correo</button>
					</div>
					<div class="mt30 ml15 mr15 hidden" id="register-container">
						<form class="form-horizontal" id="frm-register">
							{{ csrf_field() }}
							<input type="hidden" id="facebook_id">
							<input type="hidden" id="google_id">
							<div class="form-group clearfix">
								<input class="form-control" placeholder="RUC o DNI" id="empresa_ruc" name="empresa_ruc">
							</div>
							<div class="form-group clearfix">
								<input class="form-control" placeholder="Nombres y Apellidos" id="nombre" name="nombre">
							</div>
							<div class="form-group clearfix">
								<input class="form-control" placeholder="Correo Electrónico" id="email" name="email">
							</div>
							<div class="form-group clearfix">
								<input class="form-control" placeholder="Número celular" id="telefono" name="telefono">
							</div>
							<div class="form-group clearfix">
								<select class="form-control" id="local_id" name="local_id">
									@foreach($locales as $local)
									<option value="{{ $local->id }}">{{ $local->direccion }}</option>
									@endforeach
								</select>
							</div>
			                <div class="form-group clearfix">
								<div class="checkbox-custom mb5">
									<input type="checkbox" id="chk_terms">
									<label for="checkboxDefault3"><small>Estoy de acuerdo con <span class="text-primary">los términos de uso</span></small></label>
								</div>
			                </div>
			                <div class="form-group clearfix">
			                	<button class="btn btn-primary btn-block">Crea tu oficina ahora!</button>
			                </div>
						</form>
					</div>
				</div>
				<div class="col-md-6 hidden" id="second-step">
					<h3 class="text-center text-warning"><strong>!FELICIDADES!</strong></h3>
					<div class="mt20">Has configurado tu oficina con <b>ÉXITO!</b> por <b>7 días</b> podrás hacer uso de los siguientes beneficios:</div>
					<div class="mt50 visible-xs"></div>
					<div class="mt15" id="pbx-info"><span class="text-primary"><b>TU NÚMERO EMPRESARIAL ES:</b></span> <span id="pbx-number"></span></div>
					<div class="mt5"><small>¡Sorprende a tus clientes con un mensaje de bienvenida y recibe las llamadas en tu celular!</small></div>
					<div class="mt15"><span class="text-primary"><b>TU DIRECCIÓN ES:</b></span> <span id="local-address"></span></div>
					<div class="row mt10">
						<div class="col-md-6">
							<img src="/images/hq/hq_1.jpg" width="100%" height="160px">
						</div>
						<div class="col-md-6">Usa esta dirección para <b>recibir tu correspondencia</b>. Nosotros la guardaremos y te enviaremos un mensaje para que puedas recogerla. Así mismo tendrás <b>horas disponibles</b> en salas de reuniones para llevar a tus clientes.</div>
					</div>
				</div>
			</div>
			<div class="row mb20">
				<div class="col-md-12 text-center" style="font-size: 180%;">Más de <span class="text-primary"><strong>5,000 emprendedores</strong></span> forman parte de nuestro concepto ... <span class="text-warning"><strong>¡Solo faltas tú!</strong></span></div>
			</div>
			<div class="row">
				<div class="col-md-12 text-center">Somos una plataforma que te brinda acceso a una oficina en más de <strong>5 centros empresariales PREMIUM</strong> a nivel nacional. Directorios, salas de reuniones y todos los servicios incluidos, para que puedas desarrollar tu negocio con <strong>éxito</strong>.</div>
			</div>
		</div>
	</section>
    <footer id="footer">
        <div class="container mw850">
            <div class="row">
                <div class="col-md-12 text-center">
					<img src="{{ asset('/images/sae_transparente.png') }}" style="width: 128px;">
                </div>
            </div>
        </div>
    </footer>
	<script src="{{ asset('/js/jq.js') }}"></script>
	<script src="{{ asset('/js/register.js') }}"></script>
</body>
</html>