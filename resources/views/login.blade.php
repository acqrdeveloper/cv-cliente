<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login | Súmate al Éxito</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700'>
  <link rel="stylesheet" type="text/css" href="{{ asset("appdev/css/skin/default_skin/css/theme.min.css") }}">
  <link rel="stylesheet" type="text/css" href="{{ asset("appdev/css/admin-tools/admin-forms/css/admin-forms.min.css") }}">
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
        <div class="admin-form theme-info mw500" id="login">
          <div class="row table-layout">
            <a href="dashboard.html" title="Return to Dashboard">
              <img src="{{ asset("/images/logo.png") }}" title="Centros Virtuales" class="center-block img-responsive" style="max-width: 275px;">
            </a>
          </div>
          <div class="panel mt30 mb25">
            @if(!is_null(app('request')->input('error')))
            <div class="alert alert-danger text-center" id="alert-message">{{ app('request')->input('error') }}</div>
            @endif
            <form method="post" action="/login" id="contact">
              {{ csrf_field() }}
              <div class="panel-body bg-light p25 pb15">
                <div class="section row">
                  <div class="col-md-6">
                    <a href="{{ route('facebook.login') }}" class="button btn-social facebook span-left btn-block">
                      <span>
                        <i class="fa fa-facebook"></i>
                      </span>Facebook</a>
                  </div>
                  <div class="col-md-6">
                    <a href="{{ route('google.login') }}" class="button btn-social googleplus span-left btn-block">
                      <span>
                        <i class="fa fa-google-plus"></i>
                      </span>Google+</a>
                  </div>
                </div>
                <div class="section-divider mv30">
                  <span>O</span>
                </div>
                <div class="section">
                  <label for="username" class="field-label text-muted fs18 mb10">E-mail</label>
                  <label for="username" class="field prepend-icon">
                    <input type="text" name="email" id="email" class="gui-input" placeholder="Ingresar email">
                    <label for="username" class="field-icon">
                      <i class="fa fa-user"></i>
                    </label>
                  </label>
                </div>
                <div class="section">
                  <label for="username" class="field-label text-muted fs18 mb10">Contraseña</label>
                  <label for="password" class="field prepend-icon">
                    <input type="password" name="password" id="password" class="gui-input" placeholder="Ingresar contraseña">
                    <label for="password" class="field-icon">
                      <i class="fa fa-lock"></i>
                    </label>
                  </label>
                </div>
              </div>
              <div class="panel-footer clearfix">
                <button type="submit" class="button btn-primary mr10 pull-right">Iniciar Sesión</button>
                <label class="switch ib switch-primary mt10">
                  <input type="checkbox" name="remember" id="remember" checked>
                  <label for="remember" data-on="YES" data-off="NO"></label>
                  <span>Recuerdame</span>
                </label>
              </div>
            </form>
          </div>
          <div class="login-links">
            <p>
              <a href="pages_login-alt.html" class="active" title="Sign In">¿Olvidaste la contraseña?</a>
            </p>
          </div>
          <div class="login-links hidden">
            <a href="pages_forgotpw(alt).html" class="active" title="Sign In">Sign In</a> |
            <a href="pages_register(alt).html" class="" title="Register">Register</a>
          </div>
        </div>
      </section>
    </section>
  </div>
  <script src="{{ asset("js/jq.js") }}"></script>
  <script src="{{ asset("js/utility.js") }}"></script>
  <script src="{{ asset("js/core.js") }}"></script>
  <!-- Page Javascript -->
  <script type="text/javascript">
  jQuery(document).ready(function() {
    "use strict";
    Core.init();
    setTimeout(function(){
      $('#alert-message').addClass('hidden');
      $('#alert-message').empty();
    }, 7500);
  });
  </script>
</body>
</html>
