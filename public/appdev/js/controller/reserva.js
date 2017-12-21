define(['app','angular'], function(app, angular){
	app.controller('ReservaCtrl', controller);
	controller.$inject = ['$http', '$stateParams', '$timeout', '$uibModal', 'toastr', 'ListSrv'];
	function controller($http, $stateParams, $timeout, $uibModal, toastr,  listSrv){
		var vm = this;
		vm.years         = listSrv.years();
		vm.months        = listSrv.months();
		vm.estadoreservas = listSrv.estadoreserva();
		vm.estadoreservaarray  = listSrv.estadoreservaarray();
        vm.methods = {
        	init: function init(){
        		if( !window.Laravel.reserva ){
        			window.Laravel.reserva = {};
                }
                if( !window.Laravel.reserva.data || !window.Laravel.reserva.params.anio ){
					vm.data   = {};
					vm.params = {
						estadoreserva: {value: '-', name: 'Estado'},
						anio: ( new Date() ).getFullYear(),
						mes:  ( new Date() ).getMonth()+1,
						limite: 20,
						pagina: 1,
                        loading:false,
                        now: Math.floor(Date.now() /1000)
					};
					window.Laravel.reserva.params = angular.copy( vm.params );
                    window.Laravel.reserva.data   = {};
        			vm.methods.search();
        		}else{
					vm.params = angular.copy( window.Laravel.reserva.params );
					vm.data   = angular.copy( window.Laravel.reserva.data );
        		}
        	},search:function search(){
                vm.params.loading = true;
            	param = angular.copy( vm.params );
            	param.estado = angular.copy( vm.params.estadoreserva.value );
                $http({
                    url: "/reserva/search",
                    method: 'GET',
                    params: param
                }).then(function(r) {
                    if( r.status == 200 ){
                        window.Laravel.reserva.data = r.data;
                        vm.data   = angular.copy( window.Laravel.reserva.data );
                    }
                }, function(){
                    toastr.error('No se pudo obtener la reserva.');
                }).finally(function() {
                    vm.params.loading = false;
                    window.Laravel.reserva.params = angular.copy( vm.params );
                    vm.params.now = Math.floor(Date.now() /1000);
                });
        	},cancel:function cancel($index, id){
                $uibModal.open({
                    animation: true,
                    templateUrl: '/modals/reserva_delete.html',
                    controller: ['$uibModalInstance', 'items',modalController],
                    controllerAs: 'ctrl',
                    resolve: {
                        items: function(){
                            return {'id': id, 'index': $index};
                        }
                    }
                }).result.then(function(){}, function(){});
            },filterSearch: function filterSearch(){
        		vm.params.pagina = 1;
        		vm.methods.search();
        	}
        };
        vm.methods.init();

        function modalController(instance, items){
            var ctrl = this;
            ctrl.close = function(){
                instance.dismiss('close');
            };
            ctrl.send = function(){
                vm.params.loading = true;
                ctrl.deleting = true;
                $http({
                    url: "/reserva",
                    method: 'DELETE',
                    params: {"reserva_id": items.id}
                }).then(function(r) {
                    if( (r.data.load*1) > 0 ){
                        window.Laravel.reserva.data.rows[items.index].estado = "E";
                        vm.data   = angular.copy( window.Laravel.reserva.data );
                        toastr.success('Reserva eliminada');
                        ctrl.close();
                    } else {
                        toastr.error(r.data.message);
                    }
                }, function(e){
                    toastr.error(e.data.message);
                }).finally(function() {
                    vm.params.loading = false;
                    ctrl.deleting = false;
                    window.Laravel.reserva.params = angular.copy( vm.params );
                    vm.params.now = Math.floor(Date.now() /1000);
                });
            };
        }
	}
});