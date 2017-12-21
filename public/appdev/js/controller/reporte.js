define(['app','angular'], function(app, angular){
	app.controller('ReporteCtrl', controller);
	controller.$inject = ['$http', '$stateParams', '$timeout', '$uibModal', 'toastr', 'ListSrv'];
	function controller($http, $stateParams, $timeout, $uibModal, toastr,  listSrv){
		var vm = this;
		vm.years    = listSrv.years();
		vm.months   = listSrv.months();
		vm.reportes = listSrv.reportes();
        vm.methods = {
        	init: function init(){
        		if( !window.Laravel.reporte ){
        			window.Laravel.reporte = {};
					vm.data   = {};
					vm.params = {
						reporte: { value:"cdr", name:"Historial de Llamadas" },
						anio: ( new Date() ).getFullYear(),
						mes:  ( new Date() ).getMonth()+1,
						estado: '-',
						limite: 20,
						pagina: 1,
                        loading:false
					};
					window.Laravel.reporte.params = angular.copy( vm.params );
                    window.Laravel.reporte.data   = {};
        			vm.methods.search();
        		}else{
					vm.params = angular.copy( window.Laravel.reporte.params );
					vm.data   = angular.copy( window.Laravel.reporte.data );
        		}

                vm.columns = listSrv.getReportColums(vm.params.reporte.value);
                vm.rsColumns = listSrv.getReportResultSetColumns(vm.params.reporte.value);

        	},resetSearch: function resetSearch(){
                vm.params.estado = '-';
                vm.methods.filterSearch();
            },filterSearch: function filterSearch(){
        		vm.params.pagina = 1;
        		vm.methods.search();
            },search: function search(){




                vm.params.loading = true;
            	vm.columns = listSrv.getReportColums(vm.params.reporte.value);
                vm.rsColumns = listSrv.getReportResultSetColumns(vm.params.reporte.value);
                param = angular.copy( vm.params );
            	param.reporte = angular.copy( vm.params.reporte.value );
                $http({
                    url: "/reporte/"+vm.params.reporte.value,
                    method: 'GET',
                    params: param
                }).then(function(r) {
                    if( r.status == 200 ){
                        window.Laravel.reporte.data[vm.params.reporte.value] = r.data;
                        vm.data   = angular.copy( window.Laravel.reporte.data );
                    }
                }, function(){
                    toastr.error('No se pudo obtener el reporte.');
                }).finally(function() {
                    vm.params.loading = false;
                    window.Laravel.reporte.params = angular.copy( vm.params );
                });




                
            },detalle: function detalle(factura_id){
                vm.params.loading = true;
                $http({
                    url: "/reporte/factura_item?factura_id="+factura_id,
                    method: 'GET'
                }).then(function(r) {
                    if( r.status == 200 ){
                        $uibModal.open({
                            animation: true,
                            templateUrl: '/modals/factura_detalle.html',
                            controller: ['$uibModalInstance', 'items', modalController],
                            controllerAs: '$ctrl',
                            resolve: { items: function(){ return { data:r.data }; } }
                        }).result.then(function(){}, function(){});
                    }
                }, function(){
                    toastr.error('No se pudo obtener el reporte.');
                }).finally(function() {
                    vm.params.loading = false;
                });
            },pdf: function pdf( ruc, comprobante, serie, numero ){
                window.open('http://service.centrosvirtuales.com/comprobante/pdf/'+ ruc +'/'+ comprobante +'/'+ serie +'/'+ numero );
            },xls: function xls(){
                param = angular.copy( vm.params );
                param.reporte = angular.copy( vm.params.reporte.value );
                var str = "";
                for (var key in param) {
                    if (str !== "") {
                        str += "&";
                    }
                    str += key + "=" + param[key];
                }
                console.log( str );
                window.open( "/export/"+vm.params.reporte.value+"?"+str );
            },invitados: function invitados(reservaID){
                window.open( "/export/invitado?reserva_id="+reservaID );                
            }


        };
        var modalController = function($uibModalInstance, items){
            var $ctrl = this;
            $ctrl.params = {
                nota: '',
            };
            if(items !== undefined){
                if(items.data !== undefined){
                    $ctrl.data = items.data;
                }
            }
            $ctrl.close = function(){
                $uibModalInstance.dismiss('cancel');
            };
            return $ctrl;
        };
        vm.methods.init();
	}
});