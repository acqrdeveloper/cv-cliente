define(['app','angular'], function(app, angular){
	app.controller('BandejaCtrl', controller);
	controller.$inject = ['$http', '$stateParams', '$timeout', '$uibModal', 'toastr', 'ListSrv'];
	function controller($http, $stateParams, $timeout, $uibModal, toastr,  listSrv){
		var vm = this;
		vm.years         = listSrv.years();
		vm.months        = listSrv.months();
		vm.asuntos       = listSrv.asuntos();
		vm.asuntoarray   = listSrv.asuntoarray();
        vm.empleado      = listSrv.empleado();
		vm.empleadoarray = listSrv.empleadoarray();
		vm.tipomensajes  = listSrv.tipomensajes();
        vm.methods = {
        	init: function init(){
        		if( !window.Laravel.bandeja ){
        			window.Laravel.bandeja = {};
					vm.data   = {};
					vm.params = {
						asunto: {value: '-', name: 'Asunto'},
						tipomensaje: {value:"received", name:"Recibido"},
						anio: ( new Date() ).getFullYear(),
						mes:  ( new Date() ).getMonth()+1,
						limite: 20,
						pagina: 1,
                        loading:false
					};
					window.Laravel.bandeja.params = angular.copy( vm.params );
                    window.Laravel.bandeja.data   = {};
        			vm.methods.search();
        		}else{
					vm.params = angular.copy( window.Laravel.bandeja.params );
					vm.data   = angular.copy( window.Laravel.bandeja.data );
        		}
        	},search:function search(){
                vm.params.loading = true;
            	param = angular.copy( vm.params );
            	param.asunto = angular.copy( vm.params.asunto.value );
            	param.tipomensaje = angular.copy( vm.params.tipomensaje.value );
                $http({
                    url: "/bandeja/" + param.tipomensaje + "/C/" + window.Laravel.profile.id,
                    method: 'GET',
                    params: param
                }).then(function(r) {
                    if( r.status == 200 ){
                        window.Laravel.bandeja.data = r.data;
                        vm.data   = angular.copy( window.Laravel.bandeja.data );
                    }
                }, function(){
                    toastr.error('No se pudo obtener sus mensajes.');
                }).finally(function() {
                    vm.params.loading = false;
                    window.Laravel.bandeja.params = angular.copy( vm.params );
                });
        	},filterSearch: function filterSearch(){
        		vm.params.pagina = 1;
        		vm.methods.search();
        	},showDetail: function showDetail(m, indice){
                var mid = (m.padre_id>0)?m.padre_id:m.bandeja_id;
	            if((m.leido*1) === 0 && vm.params.tipomensaje.value == 'received'){
	                listSrv.makeRead(m.bandeja_id);
	                vm.data.rows[indice].leido = '1';
	                window.Laravel.bandeja.data = angular.copy( vm.data );
                    window.iVariables.inbox = window.iVariables.inbox - 1;
	            }
	            disableresponse = m.de === window.Laravel.profile.id ? '1' :'0';
	            $uibModal.open({
	                animation: true,
	                templateUrl: '/modals/bandeja_detalle.html',
	                controller: ['$uibModalInstance', 'items', detailCtrl],
	                controllerAs: 'ctrl',
                    size:'lg',
	                resolve: {
	                    items: {'message_id': mid, 'de': m.de, 'disabled': disableresponse }
	                }
	            }).result.then(function(){}, function(){});
	        },showOpenNew: function showOpenNew(m, indice){
                $uibModal.open({
                    animation: true,
                    templateUrl: '/modals/bandeja_create.html',
                    controller: ['$uibModalInstance', 'items', openNewCtrl],
                    controllerAs: 'ctrl',
                    resolve: {
                        items: {
                            asuntos: vm.asuntos,
                            years: vm.years,
                            months: vm.months,
                            empleados: vm.empleado,
                            sgtemes :window.Laravel.profile.plan_id === 31 ? false : true
                        }
                    }
                }).result.then(function(){}, function(){});
            }
        };

        function openNewCtrl(instance, items){
            var ctrl     = this;
            ctrl.sending = false;
            ctrl.params  = {};
            ctrl.aux     = {};
            ctrl.aux.empleados = items.empleados;
            ctrl.aux.asuntos   = items.asuntos;
            ctrl.aux.years     = items.years;
            ctrl.aux.months    = items.months;
            console.log( items.sgtemes );
            ctrl.aux.sgtemes   = items.sgtemes;
            ctrl.params.a      = {name:"Atencion al Cliente", value:"3"};
            ctrl.params.asunto = {value:"-", name:"Asunto"};
            ctrl.params.anio   = ( new Date() ).getFullYear();
            ctrl.params.mes    = ( new Date() ).getMonth()+1;
            ctrl.params.tipo   = "R";
            ctrl.params.pago   = "N";
            ctrl.params.horas  = 1;
            ctrl.params.deposito = "";
            ctrl.params.obs      = "";
            ctrl.close = function(){
                instance.dismiss('close');
            };

            ctrl.send = function send(){
                ctrl.sending = true;
                param = angular.copy( ctrl.params );
                param.asunto = param.asunto.value;
                param.a = param.a.value;
                if( ctrl.params.asunto.value == 'H' ){
                    /*/
                    if( param.horas > 10 || param.horas < 1 ){
                        alert( "Horas deben ser entre 1 a 10" );
                        break;
                    }
                    */
                    param.mensaje = {
                        anio: param.anio,
                        mes: ((param.mes*1)),
                        horas: param.horas,
                        tipo: param.tipo,
                        pago: param.pago,
                        deposito: param.deposito,
                        obs: param.obs,
                    };
                    param.mensaje = JSON.stringify( param.mensaje );
                }else if( ctrl.params.asunto.value == 'A' ){
                    param.mensaje = {
                        fecha: param.fecha,
                        deposito: param.deposito,
                        obs: param.obs
                    };
                    param.mensaje = JSON.stringify( param.mensaje );
                }

                $http({
                    url: "/bandeja/create",
                    method: 'POST',
                    params: param
                }).then(function(r) {
                    if( r.status == 200 ){
                        vm.methods.search();
                        toastr.success(r.data.message);
                        ctrl.close();
                    }
                }, function(){
                    toastr.error('No se pudo obtener sus mensajes.');
                }).finally(function() {
                    ctrl.sending = false;
                });
            };
        }


        function detailCtrl(instance, items){
            var ctrl = this;

            ctrl.close = function(){
                instance.dismiss('close');
            };

            ctrl.messages = [];
            ctrl.loading = false;
            ctrl.sending = false;
            ctrl.empleados = vm.empleadoarray;//auxs.empleados;
            console.log( items.a );
            ctrl.params = {
                a: items.de,
                asunto: 'M',
                a_tipo: 'E',
                de: window.Laravel.profile.id,
                de_tipo: 'C',
                empresa_id: window.Laravel.profile.id,
                mensaje: '',
                padre_id: items.message_id,
                disabled: items.disabled
            };

            ctrl.send = function send(){
                ctrl.sending = true;
                // url: /bandeja/mensaje
                var p =  angular.copy(ctrl.params);
                console.log( p );
                listSrv.sendMessage(p).then(function(r){
                    toastr.success(r.data.message);
                    search();
                    ctrl.params.mensaje = '';
                }).catch(function(e){
                    toastr.error(e.data.message);
                }).finally(function(){
                    ctrl.sending = false;
                });
            };

            function search(){
                ctrl.loading = true;
                listSrv.getDetails(items.message_id).then(function(r){
                    ctrl.messages = r.data.rows;
                    for(var i = 0; i< ctrl.messages.length; i++){
                        if(ctrl.messages[i].asunto === 'H' || ctrl.messages[i].asunto === 'A'){
                            ctrl.messages[i].mensaje = JSON.parse(ctrl.messages[i].mensaje);
                        }
                    }
                }).finally(function(){ctrl.loading = false;});
            }
            search();
        }
        vm.methods.init();
	}
});