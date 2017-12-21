define(['app','io'], function(app, io){
	app.controller('AppCtrl', controller);
	controller.$inject = ['$uibModal','$http' ,'$timeout', '$state', '$filter'];
	function controller(modal, http, $timeout, $state, $filter){
		Core.init();
		var vm = this;
		var socket = null;

		vm.horarios = window.iVariables.horarios;

		var fn = {
			init: init,
			openModal: openModal
		};

		angular.extend(vm, {
			fn: fn,
			profile: angular.copy( window.Laravel.profile ),
			static: window.iVariables
		});

        function init(){
        	//console.log('Iniciando...');

	        socket = io.connect(window.Laravel.ws);

	        socket.on('connect', function(){
	        	setWS(socket.id);
	        });

	        socket.on('fromSystem', function(data){
	        	$timeout(function(){
		        	window.iVariables.inbox++;
	        	}, 100);

	        	try {
	        		launchNotification(data.from, data.message, false, function(){
	        			$state.go('bandeja');
	        		});
	        	} catch (e) {
	        	}
	        });

	        // horarios
	        http.get('/dashboard/horario').then(function(r){

	        	angular.forEach(r.data, function(obj, i){

	        		var idx = $filter('findInArray')(window.iVariables.horarios, 'id', (obj.local_id + '_' + obj.modelo_id));

	        		if(idx<0){
	        			window.iVariables.horarios.push({
	        				id: obj.local_id + '_' + obj.modelo_id,
	        				local_nombre: obj.local_nombre,
	        				modelo_nombre: obj.modelo_nombre,
	        				times: [{text: obj.texto, hini:obj.hini, hfin:obj.hfin}]
	        			});
	        		} else {
	        			window.iVariables.horarios[idx].times.push({
	        				text: obj.texto,
	        				hini: obj.hini,
	        				hfin: obj.hfin
	        			});
	        		}

	        	});

	        	//console.log(window.iVariables.horarios);
	        }, function(e){});
        }

		function openModal(view){
			modal.open({
				animation: true,
				templateUrl: '/views/modals/' + view + '.html',
				controller: ['$uibModalInstance', function(instance){
					var ctrl = this;
					ctrl.close = function(){
						instance.dismiss('cancel');
					};
					ctrl.times = window.iVariables.horarios;
				}],
				controllerAs: 'ctrl'
			}).result.then(function(){}, function(){});
		}

        function setWS(id){
        	http({url:'/set_ws/' + id, method:'PUT'}).then(function(data){
        		//console.log(data);
        	}, function(e){
   				setWS(id);
        	});
        }

        function launchNotification(titleText, bodyText, onVisible, onClick){

	        onVisible = onVisible | false;

	        var hidden, visibilityChange;
	        if (typeof document.hidden !== 'undefined') {
	            // Opera 12.10, Firefox >=18, Chrome >=31, IE11
	            hidden = 'hidden';
	            visibilityChangeEvent = 'visibilitychange';
	        } else if (typeof document.mozHidden !== 'undefined') {
	            // Older firefox
	            hidden = 'mozHidden';
	            visibilityChangeEvent = 'mozvisibilitychange';
	        } else if (typeof document.msHidden !== 'undefined') {
	            // IE10
	            hidden = 'msHidden';
	            visibilityChangeEvent = 'msvisibilitychange';
	        } else if (typeof document.webkitHidden !== 'undefined') {
	            // Chrome <31 and Android browser (4.4+ !)
	            hidden = 'webkitHidden';
	            visibilityChangeEvent = 'webkitvisibilitychange';
	        }

	        if(onVisible && !document[hidden]){
	            return false;
	        }

	        var notification = window.Notification || window.mozNotification || window.webkitNotification;

	        if ('undefined' === typeof notification)
	            alert('Web notification not supported');
	        else
	            notification.requestPermission(function(permission){});

	        if ('undefined' === typeof notification)
	            return false;       //Not supported....
	    
	        var noty = new notification(
	                titleText, {
	                    body: bodyText,
	                    dir: 'auto', // or ltr, rtl
	                    lang: 'EN', //lang used within the notification.
	                    tag: 'notificationPopup', //An element ID to get/set the content
	                    icon: '/images/icon.png' //The URL of an image to be used as an icon
	                }
	        );

	        noty.onclick = function(){
	            //console.log('notification.Click');
	            onClick();
	            noty.close();
	        };

	        noty.onerror = function () {
	          //console.log('notification.Error');
	        };
	        noty.onshow = function () {
	          //console.log('notification.Show');
	        };
	        noty.onclose = function () {
	          //console.log('notification.Close');
	        };

	        return true;
        }

        function timeController(instance){
        	var ctrl = this;
        	ctrl.close = close;
        	function close(){
        		instance.dismiss('close');
        	}
        }
	}
});