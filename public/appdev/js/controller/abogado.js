define(['app','angular'], function(app, angular){
	app.controller('AbogadoCtrl', controller);
	controller.$inject = ['$http', '$stateParams', '$timeout', '$uibModal', 'toastr', 'ListSrv'];
	function controller($http, $stateParams, $timeout, $uibModal, toastr,  listSrv){
		var vm     = this;
		vm.years   = listSrv.years();
		vm.months  = listSrv.months();
        vm.methods = {
        	init: function init(){
        		if( !window.Laravel.abogado ){
        			window.Laravel.abogado = {};
					vm.data   = {};
					vm.params = {
						anio: ( new Date() ).getFullYear(),
						mes:  ( new Date() ).getMonth()+1,
						limite: 20,
						pagina: 1,
                        loading:false,
                        estado:"-",
                        filtrotipo:"caso",
                        filtro:""
					};
					window.Laravel.abogado.params   = angular.copy( vm.params );
                    window.Laravel.abogado.data     = {};
        			vm.methods.search();
        		}else{
					vm.params   = angular.copy( window.Laravel.abogado.params );
					vm.data     = angular.copy( window.Laravel.abogado.data );
        		}
        	},search:function search(){
                vm.params.loading = true;
            	param = angular.copy( vm.params );
                $http({
                    url: "/abogado/search",
                    method: 'GET',
                    params: param
                }).then(function(r) {
                    if( r.status == 200 ){
                        window.Laravel.abogado.data = r.data;
                        vm.data   = angular.copy( window.Laravel.abogado.data );
                    }
                }, function(){
                    toastr.error('No se pudo obtener el abogado.');
                }).finally(function() {
                    vm.params.loading = false;
                    window.Laravel.abogado.params = angular.copy( vm.params );
                });
        	},putestado:function( index, id, estado ){
                vm.params.loading = true;
                $http({
                    url: "/abogado/"+id+"/"+estado,
                    method: 'PUT',
                }).then(function(r) {
                    if( r.status == 200 ){
                    	vm.data.rows[index].estado  = estado;
                        window.Laravel.abogado.data = angular.copy( vm.data );
                    }
                }, function(){
                    toastr.error('No se pudo alterar el estado.');
                }).finally(function() {
                    vm.params.loading = false;
                });
        	},filterSearch: function filterSearch(){
        		vm.params.pagina = 1;
        		vm.methods.search();
        	},openModal: function openModal(){
				$uibModal.open({
					animation: true,
					templateUrl: '/modals/abogado_create.html',
					controller: ['$uibModalInstance','items', modalController],
					controllerAs: '$ctrl',
					resolve: {
						items: function(){
							return {};
						}
					}
				}).result.then(function(){}, function(){});
			}
        };
        vm.methods.init();
		var modalController = function($uibModalInstance, items){
			var $ctrl = this;
			$ctrl.params = {
				caso: "",
				demandante: "",
				demandado: ""
			};

			$ctrl.close = function(){
				$uibModalInstance.dismiss('cancel');
			};

			$ctrl.send = function(){
				$ctrl.sending = true;
                $http({
                    url: "/abogado",
                    method: 'POST',
                    params: $ctrl.params
                }).then(function(r) {
                    if( r.status === 200 ){

                        if( vm.data.rows === undefined ){
                            vm.data.rows = {};
                        }

                    	vm.data.rows.unshift( r.data );
                    	$ctrl.close();
                        window.Laravel.abogado.data = angular.copy( vm.data );
                    }
                }, function(){
                    toastr.error('No se pudo crear el caso.');
                }).finally(function() {
					$ctrl.sending = false;
                });
			};
		};
	}
});