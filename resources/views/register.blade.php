<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Registraté | Súmate al Éxito</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700'>
  <link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600'>
  <link rel="stylesheet" type="text/css" href="{{ asset("css/app.min.css") }}">
  <link rel="shortcut icon" href="/favicon.ico">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
  <![endif]-->
</head>
<body class="external-page external-alt sb-l-c sb-r-c">
  <div id="main" class="animated fadeIn">
    <section id="content_wrapper">
      <section id="content">
        <div class="admin-form theme-primary mw600" style="margin-top: 3%;" id="register">
          <div class="row table-layout">
            <a href title="Return to Dashboard">
              <img src="{{ asset("/images/logo.png") }}" title="Centros Virtuales" class="center-block img-responsive" style="max-width: 275px;">
            </a>
          </div>
          <div id="alert-message"></div>
          <div id="user-content">
            <div class="panel mt40">
              <form id="frm-register">
                {{ csrf_field() }}
                <input type="hidden" id="facebook_id">
                <input type="hidden" id="google_id">
                <div class="panel-body bg-light p25 pb15">
                  <div class="section">
                    <label for="ruc" class="field prepend-icon">
                      <input type="text" name="empresa_ruc" id="ruc" class="gui-input" placeholder="RUC o DNI" autocomplete="off" required>
                      <label for="ruc" class="field-icon">
                        <i class="fa fa-id-card-o"></i>
                      </label>
                    </label>
                  </div>
                  <div class="section">
  	                <label for="nombre" class="field prepend-icon">
  	                  <input type="text" name="nombre" id="nombre" class="gui-input" placeholder="Nombres" autocomplete="off" required>
  	                  <label for="nombre" class="field-icon">
  	                    <i class="fa fa-user"></i>
  	                  </label>
  	                </label>
                  </div>
                  <div class="section">
                    <label for="email" class="field prepend-icon">
                      <input type="email" name="email" id="email" class="gui-input" placeholder="Correo electrónico" autocomplete="off" required>
                      <label for="email" class="field-icon">
                        <i class="fa fa-envelope"></i>
                      </label>
                    </label>
                  </div>
                  <div class="section">
                    <label for="telefono" class="field prepend-icon">
                      <input type="text" name="telefono" id="telefono" class="gui-input" placeholder="Teléfono" autocomplete="off" required>
                      <label for="email" class="field-icon">
                        <i class="fa fa-phone"></i>
                      </label>
                    </label>
                  </div>
                  <!--<hr class="alt short">-->
                </div>
                <div class="panel-footer clearfix">
                  <button type="submit" class="button btn-primary mr10 pull-right" id="btn-register">Registrar</button>
          				<label class="option mt10">
          					<input type="checkbox" id="chk_terms">
          					<span class="checkbox"></span> Estoy de acuerdo con <a>los términos de uso</a>
          				</label>
                </div>
              </form>
            </div>
            <hr class="alt mt40 mb30 mh70">
            <div class="section row center-block" style="width: 550px;">
              <div class="col-md-6">
                <a href="#" class="button btn-social facebook span-left btn-block" id="btn-fb-register" data-auth="facebook">
                  <span>
                    <i class="fa fa-facebook"></i>
                  </span>Facebook</a>
              </div>
              <div class="col-md-6">
                <a href="#" class="button btn-social googleplus span-left btn-block" id="btn-goo-register" data-auth="google">
                  <span>
                    <i class="fa fa-google-plus"></i>
                  </span>Google+</a>
              </div>
            </div>
            <div class="login-links">
              <p>¿Ya estás registrado?
                <a href="/login" class="active" title="Sign In">Inicia sesión</a>
              </p>
            </div>
          </div>
          <div id="pbx-content" class="hidden mt40">
            <div class="text-center"><b>Dinos donde quieres recibir tus llamadas</b></div><br>
            <div>Coloca el número celular donde quieras recibir las llamadas de tu central telefónica, puedes colocar hasta 3 números distintos</div>
            <div class="panel mt40">
              <form id="frm-pbx-create" class="form-horizontal">
                <p></p>
                <div class="panel-body">
                  <div class="form-group">
                    <div class="col-md-6">
                      <label>Anexo</label>
                      <input type="text" class="form-control" readonly value="101" name="ext[]">
                    </div>
                    <div class="col-md-6">
                      <label>Celular</label>
                      <input type="text" class="form-control" name="celular[]">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-md-6">
                      <label>Anexo</label>
                      <input type="text" class="form-control" readonly value="102" name="ext[]">
                    </div>
                    <div class="col-md-6">
                      <label>Celular</label>
                      <input type="text" class="form-control" name="celular[]">
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="col-md-6">
                      <label>Anexo</label>
                      <input type="text" class="form-control" readonly value="103" name="ext[]">
                    </div>
                    <div class="col-md-6">
                      <label>Celular</label>
                      <input type="text" class="form-control" name="celular[]">
                    </div>
                  </div>
                </div>
                <div class="panel-footer clearfix">
                  <button type="submit" class="button btn-primary mr10 pull-right">Crear Central Telefónica</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
    </section>
  </div>
  <script src="{{ asset('/js/jq.js') }}"></script>
  <script src="{{ asset('/js/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
  <script src="{{ asset('/js/utility.js') }}"></script>
  <script src="{{ asset('/js/core.js') }}"></script>
  <script type="text/javascript" src="{{ asset('/js/register.js') }}"></script>
</body>
</html>