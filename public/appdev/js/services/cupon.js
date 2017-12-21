define(['app'], function(app){
	app.factory('CuponSrv', service);
	service.$inject = ['$http'];

	function service($http){
		return {
			create: function(p){
				return $http({method:'POST', url:'/cupon', data: p});
			},
			delete: function(id){
				return $http({method:'DELETE', url:'/cupon/' + id});
			},
			search: function(p){
				return $http({method:'GET', url:'/cupon/search', params: p});
			},
			validate: function(code){
				return $http({method: 'GET', url: '/cupon/valid/' + code});
			}
		};
	}
});