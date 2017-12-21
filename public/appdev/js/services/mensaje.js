define(['app'], function(app){
	app.factory('MensajeSrv', service);
	service.$inject = ['$http'];
	function service(http){
		return {
			get: function get(params, tipo, a){
				return http({
                    //url: '/bandeja/received/E/'+params.empleado,
                    url: '/bandeja/' + tipo + '/' + a + '/' + params.empleado,
                    method: 'GET',
                    params: params
                });
			},
			sendMessage: function sendMessage(params){
				return http({
                    url: '/bandeja/create',
                    method: 'POST',
                    data: params
                });
			},
			getDetails: function getDetails(message_id){
				return http({
                    url: '/bandeja/'+message_id,
                    method: 'GET'
                });
			},
			makeRead: function makeRead(message_id){
				return http({
                    url: '/bandeja/read/'+message_id,
                    method: 'PUT'
                });
			},
			sendReponse: function sendResponse(empresa_id, message_id, response){
				return http({
                    url: '/bandeja/response/' + empresa_id + '/' + message_id + '/' + response,
                    method: 'PUT'
                });
			}
		};
	}
});