define(['app'], function(app){
	app.controller('ProfileCtrl', controller);
	controller.$inject = ['ProfileSrv','toastr'];
	function controller(mySrv, toastr){
		var vm = this;
		var profile = angular.copy(window.Laravel.profile);
		var auxs = {
			active: 0,
			sending: false
		};

		var fn = {
			selectTab: function(idx){
				auxs.active = idx;
			},
			updateBilling: function(){
				auxs.sending = true;
				mySrv.updateBilling(params.billing).then(function(r){
					toastr.success(r.data.message);
					window.Laravel.profile.fac_nombre = params.billing.fac_nombre;
					window.Laravel.profile.fac_apellido = params.billing.fac_apellido;
					window.Laravel.profile.fac_email = params.billing.fac_email;
					window.Laravel.profile.fac_telefono = params.billing.fac_telefono;
					window.Laravel.profile.fac_celular = params.billing.fac_celular;
					window.Laravel.profile.fac_domicilio = params.billing.fac_domicilio;
				}).catch(function(e){
					toastr.error(e.data.message);
				}).finally(function(){
					auxs.sending = false;
				});
			},
			updateLogin: function(){
				auxs.sending = true;
				mySrv.updateLogin(params.login).then(function(r){
					toastr.success(r.data.message);
					window.Laravel.profile.preferencia_login = params.login.preferencia_login;
				}).catch(function(e){
					toastr.error(e.data.message);
				}).finally(function(){
					auxs.sending = false;
				});
			},
			updatePassword: function(){
				auxs.sending = true;
				mySrv.updatePassword(params.passwd).then(function(r){
					toastr.success(r.data.message);
					window.Laravel.profile.preferencia_contrasenia = params.passwd.pass_new_2;
				}).catch(function(e){
					toastr.error(e.data.message);
				}).finally(function(){
					auxs.sending = false;
				});
			}
		};

		var params = {
			company: {
				empresa_direccion: profile.empresa_direccion,
				empresa_nombre: profile.empresa_nombre,
				empresa_rubro: profile.empresa_rubro,
				empresa_ruc: profile.empresa_ruc
			},
			billing: {
				fac_nombre: profile.fac_nombre,
				fac_apellido: profile.fac_apellido,
				fac_email: profile.fac_email,
				fac_telefono: profile.fac_telefono,
				fac_celular: profile.fac_celular,
				fac_domicilio: profile.fac_domicilio
			},
			login: {
				preferencia_login: profile.preferencia_login
			},
			passwd: {
				pass_old: '',
				pass_new_1: '',
				pass_new_2: ''
			}
		};

		angular.extend(vm, {
			auxs: auxs,
			fn: fn,
			params: params
		});
	}
});