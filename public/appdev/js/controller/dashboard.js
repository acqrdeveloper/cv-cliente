define(['app','angular'], function(app, angular){
	app.controller('DashboardCtrl', controller);
	controller.$inject = ['$http', '$stateParams', '$timeout', '$uibModal', 'toastr', 'ListSrv'];
	function controller($http, $stateParams, $timeout, $uibModal, toastr,  listSrv){
		var vm = this;
        var now = new Date();
        var chart = new Highcharts.Chart({
            chart: {
                renderTo: 'high-pie',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie',
                height: 247
            },
            title: {
                text: ''
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: 'balance',
                colorByPoint: true,
                data: [{
                    name: 'Contestadas',
                    y: 0
                },{
                    name: 'No contestadas',
                    y: 0
                }]
            }]
        });
		vm.years         = listSrv.years();
		vm.months        = listSrv.months();

        vm.params = {
            loading: false,
            anio: now.getFullYear()*1,
            mes: (now.getMonth()*1)+1
        };

        vm.months.splice(0,1);

        if( window.Laravel.profile.preferencia_facturacion != "MENSUAL" ){
            if( ( now.getDate() * 1 ) < 15 ){
              var d = new Date();
              d.setMonth(now.getMonth() - 1);
              vm.params.anio = now.getFullYear() * 1;
              vm.params.mes = ( ( now.getMonth() * 1 ) + 1 );
            }
        }  

        vm.requestData = requestData;

        requestData();

        function requestData(){
            vm.params.loading = true;
            $http({
                url: "/dashboard/initial",
                method: 'GET',
                params: vm.params
            }).then(function(r) {
                if( r.data.load ){
                    vm.plan = r.data.data.plan;
                    cc = 0;
                    ci = 0;
                    hr = 0;
                    hp = 0;
                    if( r.data.data.recurso ){
                        console.log( r.data.data.recurso );
                        cc = r.data.data.recurso.cantidad_copias;
                        ci = r.data.data.recurso.cantidad_impresiones;
                        hr = r.data.data.recurso.horas_reunion;
                        hp = r.data.data.recurso.horas_privada;
                    }else{
                        console.log( r.data.data );
                    }
                    vm.info = {
                        cantidad_copias: cc,
                        cantidad_impresiones: ci,
                        horas_reunion: hr,
                        horas_privada: hp
                    };
                    chart.series[0].setData(r.data.data.cdr, true, false, false);
                }
            },function(){
                toastr.error('No se pudo obtener la dashboard.');
            }).finally(function() {
                vm.params.loading = false;
            });            
        }
	}
});