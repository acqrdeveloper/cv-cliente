define(['app'], function(app){

	app.factory('ListSrv', service);
	service.$inject = ['$http'];

	function service(http){
		var s = {
			getEstadosEmpresa: getEstadosEmpresa,
			getReportColums: getReportColums,
			getReportResultSetColumns: getReportResultSetColumns,
			years: getYears,
			tiposPago: getTiposPago,
			ciclos: getCiclos,
			reportes: getReportes,
			asuntos: getAsunto,
			months: getMonths,
			tipomensajes: getTipoMensaje,
			asuntoarray: getAsuntoArray,
			empleado: getEmpleado,
			empleadoarray: getEmpleadoArray,
			getDetails:getDetails,
			sendMessage:sendMessage,
			makeRead:makeRead,
			estadoreserva:getEstadoReserva,
			estadoreservaarray:getEstadoReservaArray,
			getTimes: getTimes,
			getTimes2: getTimes2
		};

		return s;

		function getDetails(message_id){
			return http({
                url: '/bandeja/'+message_id,
                method: 'GET'
            });
		}

		function sendMessage(params){
			return http({
                url: '/bandeja/create',
                method: 'POST',
                data: params
            });
		}

		function makeRead(message_id){
			return http({
                url: '/bandeja/read/'+message_id,
                method: 'PUT'
            });
		}
		
		function getAsuntoArray(){
			return {
				"Q":"Queja",
				"S":"Sugerencia",
				"M":"Mensaje",
				"H":"Horas"/*,
				"A":"Auditorio"*/
			};
		}            


		function getAsunto(){
			return [
	            {value: '-', name: 'Asunto'},
				{name:"Queja", value:"Q"},
				{name:"Sugerencia", value:"S"},
				{name:"Mensaje", value:"M"},
				{name:"Horas", value:"H"}/*,
				{name:"Auditorio", value:"A"},*/
			];
		}

		function getCiclos(){
			return [
				{value:"-", name:"Ciclo"},
				{value:"QUINCENAL", name:"Quincenal"},
				{value:"MENSUAL", name:"Mensual"}
			];
		}

		function getEstadoReserva(){
			return [
				{value: '-', name: 'Estado'},
				{value: 'A', name: 'Activo'},
				{value: 'E', name: 'Eliminado'},
				{value: 'P', name: 'Pendiente'},
			];
		}

		function getEstadoReservaArray(){
			return {
				'A': 'Activo',
				'E': 'Eliminado',
				'P': 'Pendiente'
			};
		}

		function getEstadosEmpresa(){
			return [
	            {value: '-', name: 'Estado Cliente'},
	            {value: 'A', name: 'Activo'},
	            {value: 'I', name: 'Inactivo'},
	            {value: 'P', name: 'Pendiente'},
	            {value: 'E', name: 'Eliminado'},
			];
		}

		function getMonths(){
			return ['Todos','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre'];
		}

		function getTipoMensaje(){
			return [
				{value:"received", name:"Recibido"},
				{value:"send", name:"Enviado"},
			];
		}

		function getReportes(){
			return [
				{value:"cdr", name:"Historial  de Llamadas"},
				{value:"correspondencia", name:"Correspondencia"},
				{value:"recado", name:"Recado"},
				{value:"factura", name:"Factura"},
				{value:"invitados", name:"Invitados"},
			];
		}

		function getEmpleado(){
			return [
	            {name:'Administrador', value: "2"},
				{name:"Atencion al Cliente", value:"3"},
				{name:"Cobranza", value:"4"},
			];
		}

		function getEmpleadoArray(){
			empleados = [];
			empleados["1"] = "Sistema";
			empleados["2"] = "Administrador";
			empleados["3"] = "Atencion Cliente";
			empleados["4"] = "Cobranza";
			return empleados;
		}

		function getReportColums(type){
			switch(type){
				case 'cdr':
					return ['Fecha', 'Origen', 'Destino', 'Tiempo', 'Estado'];
				case 'correspondencia':
					return ['Remitente','Asunto','Lugar','Creado','Entregado','Estado'];
				case 'recado':
					return ['Destinatario','Contenido','Creado','Entregado','Estado'];
				case 'factura':
					return ['Comprobante','Serie','Numero','Monto','F. Emision','F. Vencimiento','F. Limite','Estado',''];
				case 'invitados':
					return ['Evento','Fecha','Hora inicio','Hora fin', 'Sede', 'Invitados', 'Capacidad'];
				default:
					return [];
			}
		}

		function getReportResultSetColumns(type){
			switch(type){
				case 'cdr':
					return [{id:'calldate', classes:'text-center'},{id:'src', classes:'text-right'},{id:'dst', classes:'text-right'},{id:'tiempo', classes:'text-center'}];
				case 'correspondencia':
					return [{id:'remitente'},{id:'asunto'},{id:'lugar', classes:'text-center'},{id:'fecha_creacion', classes:'text-center'},{id:'entregado_a', classes:'text-center'},{id:'estado', classes:'text-center'}];
				case 'recado':
					return [{id:'para'},{id:'contenido_paquete'},{id:'fecha_creacion', classes:'text-center'},{id:'entregado_a', classes:'text-center'},{id:'estado', classes:'text-center'}];
				case 'factura':
					return [{id:'comprobante', classes:'text-center'},{id:'serie', classes:'text-center'}, {id:'numero', classes:'text-center'}, {id:'monto', classes:'text-center'}, {id:'fecha_emision', classes:'text-center'}, {id:'fecha_vencimiento', classes:'text-center'}, {id:'fecha_limite', classes:'text-center'}, {id:'estado', classes:'text-center'}];
				case 'invitados':
					return [{id:'evento_nombre'},{id:'fecha_reserva', classes:'text-center'},{id:'hora_inicio', classes:'text-center'},{id:'hora_fin', classes:'text-center'},{id:'local_nombre'},{id:'invitados', classes:'text-center'},{id:'capacidad', classes:'text-center'}];
				default:
					return [];
			}
		}

		function getTiposPago(){
			return [
				{value:"-", name:"Tipo Pago"},
				{value:"EFECTIVO", name:"Efectivo"},
				{value:"DEPOSITO", name:"Deposito"},
				{value:"GARANTIA", name:"Garantia"},
				{value:"FACTURA", name:"Factura"},
			];
		}

		function getYears(){
			var years = [];
			for(var i = (new Date()).getFullYear(); i>=2015; i--){
				years.push(i);
			}
			return years;
		}

		function getTimes(){
			return [{id:"08:00:00", value:"08:00 AM"}, {id:"08:30:00", value:"08:30 AM"}, {id:"09:00:00", value:"09:00 AM"}, {id:"09:30:00", value:"09:30 AM"}, {id:"10:00:00", value:"10:00 AM"}, {id:"10:30:00", value:"10:30 AM"}, {id:"11:00:00", value:"11:00 AM"}, {id:"11:30:00", value:"11:30 AM"}, {id:"12:00:00", value:"12:00 AM"}, {id:"12:30:00", value:"12:30 AM"}, {id:"13:00:00", value:"01:00 PM"}, {id:"13:30:00", value:"01:30 PM"}, {id:"14:00:00", value:"02:00 PM"}, {id:"14:30:00", value:"02:30 PM"}, {id:"15:00:00", value:"03:00 PM"}, {id:"15:30:00", value:"03:30 PM"}, {id:"16:00:00", value:"04:00 PM"}, {id:"16:30:00", value:"04:30 PM"}, {id:"17:00:00", value:"05:00 PM"}, {id:"17:30:00", value:"05:30 PM"}, {id:"18:00:00", value:"06:00 PM"}, {id:"18:30:00", value:"06:30 PM"}, {id:"19:00:00", value:"07:00 PM"}, {id:"19:30:00", value:"07:30 PM"}, {id:"20:00:00", value:"08:00 PM"}, {id:"20:30:00", value:"08:30 PM"}, {id:"21:00:00", value:"09:00 PM"}, {id:"21:30:00", value:"09:30 PM"}, {id:"22:00:00", value:"10:00 PM"}];
		}

		function getTimes2(){
			return [{id:"08:00:00", value:"08:00 AM"}, {id:"09:00:00", value:"09:00 AM"}, {id:"10:00:00", value:"10:00 AM"}, {id:"11:00:00", value:"11:00 AM"}, {id:"12:00:00", value:"12:00 AM"}, {id:"13:00:00", value:"01:00 PM"}, {id:"14:00:00", value:"02:00 PM"}, {id:"15:00:00", value:"03:00 PM"}, {id:"16:00:00", value:"04:00 PM"}, {id:"17:00:00", value:"05:00 PM"}, {id:"18:00:00", value:"06:00 PM"}, {id:"19:00:00", value:"07:00 PM"}, {id:"20:00:00", value:"08:00 PM"}, {id:"21:00:00", value:"09:00 PM"}, {id:"22:00:00", value:"10:00 PM"}];
		}
	}
});