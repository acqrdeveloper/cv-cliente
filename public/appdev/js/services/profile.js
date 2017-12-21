define(['app'], function(app){
	app.factory('ProfileSrv', service);
	service.$inject = ['$http'];
	function service($http){
		return {
			updateBilling: function(params){
				return $http({
					url: '/profile/setfactura',
					method: 'PUT',
					data: params
				});
			},
			updateLogin: function(params){
				return $http({
					url: '/profile/setlogin',
					method: 'PUT',
					data: params
				});
			},
			updatePassword: function(params){	
				return $http({
					url: '/profile/setpass',
					method: 'PUT',
					data: params
				});
			}
		};
	}
});