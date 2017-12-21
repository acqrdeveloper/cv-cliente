define(['app'], function(app){

	app.factory('ReservaSrv', service);
	service.$inject = ['$http'];
	function service($http){
		return {
			create: function(p){
				return $http({
					url: '/reserva',
					method: 'POST',
					data: p
				});
			},
			getAvailable: function(p){
				return $http({
                    url: '/reserva/oficina/disponibilidad',
                    method: 'GET',
                    params: p
                });
			},
			getAvailableV1: function(p){
				return $http({
                    url: '/oficina/disponibilidad.v1',
                    method: 'GET',
                    params: p
                });
			},
			getCocheras: function(reserva_id, p){
				return $http({
                    url: '/cochera/'+reserva_id+'/'+p.fecha+'/'+p.local_id+'/'+p.hini+'/'+p.hfin,
                    method: 'GET'
                });
			},
			getPrice: function(local_id, modelo_id, plan_id){
				return $http({
					url: '/reserva/auditorio/' + local_id + '/' + modelo_id +'/0',
					method: 'GET'
				});
			},
			search:  function search(params){
				return $http({
					url: '/reserva/search',
					method: 'GET',
					params: params
				});
			}
		};


	}
});