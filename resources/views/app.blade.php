<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title> | Súmate al Éxito</title>
	<link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600'>
	<link rel="stylesheet" type="text/css" href="{{ asset("css/app.min.css") }}">
	<link rel="shortcut icon" href="/favicon.ico">
</head>
<body class="dashboard-page sb-l-o sb-r-c">
	<div id="main" ui-view></div>
	<script>
		window.Laravel = {
			profile: {!! Auth::user() !!},
			at: '{{ csrf_token() }}',
			is_menu_open: false,
			ws: '{{ env('NOTIFICATION_SERVER') }}'
		};
	</script>
	<script src="{{ asset("js/jq.js") }}"></script>
	<script src="{{ asset("js/plugins/jquery-ui/jquery-ui.min.js") }}"></script>
	<script src="{{ asset("js/utility.js") }}"></script>
	<script src="{{ asset("js/core.js") }}"></script>
	<script src="{{ asset("js/plugins/highcharts/highcharts.js") }}"></script>
	<script>
	    window.iVariables = {
	        ciudades: [{id:'MIRAFLORES', value:'Miraflores'},{id:'SURCO', value:'Surco'}],
	        coffeebreak: {!! DB::table('concepto')->where('estado','A')->where('tipo','CB')->get(['id','nombre','descripcion', 'precio']) !!},
	        horarios: [],
	        inbox:    {!! DB::table('bandeja')->where( "leido", 0 )->where( "a_tipo", "C" )->where( "a", Auth::user()->id )->count() !!},
	        locales:  {!! DB::table('clocal')->where('estado','A')->get(['id','nombre','modeloids']) !!},
	        modelos:  {!! DB::table('modelo')->get(['id','nombre']) !!},
	        oficinas: {!! DB::table('oficina')->where('estado','A')->get(['id', 'nombre_o', 'local_id', 'modelo_id']) !!},
	        planes:   {!! DB::table('plan')->where('estado','A')->get(['id','nombre','cantidad_copias','cantidad_impresiones','proyector','cochera','precio']) !!}
	    };
	</script>
	@if(env('APP_ENV')=='local')
	    <script data-main="{{ asset('/main.js') }}" src="{{ asset('/node_modules/requirejs/require.js') }}"></script>
	@else
	    <script src="{{ asset('/js/app.min.js') }}?v={{ time() }}"></script>
	@endif
</body>
</html>