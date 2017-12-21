require.config({
	waitSeconds: 300,
	paths: {
		angular: './node_modules/angular/angular',
		ngAnimate: './node_modules/angular-animate/angular-animate',
		ngRoute: './node_modules/angular-route/angular-route',
		uiRouter: './node_modules/angular-ui-router/release/angular-ui-router',
		uiBootstrap: './node_modules/angular-ui-bootstrap/dist/ui-bootstrap-tpls',
		'ui.select': './node_modules/ui-select/dist/select.min',
		toastr: './node_modules/angular-toastr/dist/angular-toastr.tpls',
		io: './node_modules/socket.io-client/dist/socket.io',
		app: './appdev/js/app'
	},
	shim: {
		angular: {
			exports: 'angular'
		},
		ngAnimate: {
			deps: ['angular']
		},
		ngRoute: {
			deps: ['angular']
		},
		uiRouter: {
			deps: ['angular']
		},
		uiBootstrap: {
			deps: ['angular']
		},
		'ui.select': {
			deps: ['angular']
		},
		toastr: {
			deps: ['angular']
		}
	}
});
require([
	'angular',
	'ngAnimate',
	'ngRoute',
	'uiRouter',
	'uiBootstrap',
	'ui.select',
	'toastr',
	'app',
	'appdev/js/route',
	'appdev/js/filters',
	'appdev/js/services/cupon',
	'appdev/js/services/list',
	'appdev/js/services/profile',
	'appdev/js/services/reserva',
	'appdev/js/filters',
	'appdev/js/controller/abogado',
	'appdev/js/controller/bandeja',
	'appdev/js/controller/dashboard',
	'appdev/js/controller/full',
	'appdev/js/controller/profile',
	'appdev/js/controller/reporte',
	'appdev/js/controller/reserva',
	'appdev/js/controller/create-reserva'
], function(angular){
	angular.element(document).ready(function(){
		angular.bootstrap(document, ['CentrosApp']);
	});
});