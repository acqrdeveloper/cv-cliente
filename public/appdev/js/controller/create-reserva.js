define(['app','angular'], function(app, angular){
	app.controller('ReservaCreateCtrl', controller);
	controller.$inject = ['$filter', '$state', '$stateParams', '$uibModal', 'toastr', 'CuponSrv', 'ListSrv', 'ReservaSrv'];
	function controller($filter, $state, $stateParams, $uibModal, toastr, CuponSrv, listSrv, mySrv){

		var vm = this;
        var aux = {
            'blockCupon': false,
            'coffeebreak': angular.copy(window.iVariables.coffeebreak),
            'cocheras': [{cochera_id:0,cochera_nombre:'No deseo cochera'}],
            'dateOptions': { minDate: new Date() },
            'edit': false,
            'locales': [{id:0, nombre:"Local"}],
            'modelos': [{id:0, nombre:"Modelo"}].concat(window.iVariables.modelos),
            'oficinas': [],
            'saving': false,
            'searching_spaces': false,
            'searching_cupon': false,
            'selected_coffeebreak': window.iVariables.coffeebreak[0],
            'selected_cupon': {precio:0},
            'space_image': 'preload.jpg',
            'time_list': [],
            'times1': listSrv.getTimes(),
            'times2': listSrv.getTimes(),
            'unavailable_space': false
        };

        // delete
        aux.times2.splice(0,1);
        aux.modelos.splice(4,1);

        var params = {
            empresa_id: 0,
            audio: 'N',
            cochera_id: 0,
            coffeebreak: 'N',
            cupon: '',
            selected_cb: {
                id: 0,
                cantidad: 0,
                precio: 0
            },
            local_id: 0,
            modelo_id: 0,
            nombre: '',
            observacion: '',
            oficina_id: 0,
            reserva_id: 0,
            fecha: new Date(),
            hini: "08:00:00",
            hfin: "09:00:00",
            silla: 20,
            mesa: "0",
            detalle: []
        };

        var fn = {
            cancel: cancel,
            clearCB: clearCoffeeBreak,
            clearCompanies: clearCompanies,
            create: create,
            getAvailableV1: obtenerDisponibles,
            getTotalCost: getTotalCost,
            filterHq: filterHq,
            init: init,
            getCocheras: getCocheras,
            openCalendar: openCalendar,
            selectCoffeebreak: selectCoffeebreak,
            selectOffice: selectOffice,
            validateCupon: validateCupon
        };

        angular.extend(vm, {
            aux: aux,
            params: params,
            fn: fn
        });


        function cancel(){
            $state.go('reserva');
        }

        function clearCoffeeBreak(){
            if(params.coffeebreak === 'S'){
                params.selected_cb.id = aux.coffeebreak[0].id;
                params.selected_cb.precio = aux.coffeebreak[0].precio;
                if(params.selected_cb.cantidad<=0)
                    params.selected_cb.cantidad = 20;
            } else {
                params.selected_cb.id = 0;
                params.selected_cb.cantidad = 0;
                params.selected_cb.precio = 0;
            }
        }

        function clearCompanies(){
            aux.selected_company = '';
            params.empresa_id = 0;
        }

        function create(){
            var p = angular.copy(params);
            p.fecha = $filter('date')(p.fecha, 'yyyy-MM-dd', 'America/Lima');

            if(([2,3]).indexOf(p.modelo_id) >= 0){
                var file = document.getElementById("file_invitados");
                if(file !== null && file.files.length>0){
                    var reader = new FileReader();
                    reader.onload = function(e){
                        var allTextLines = (e.target.result).split(/\r\n|\n/);
                        p.estructura = [];
                        while(allTextLines.length){
                            var line = allTextLines.shift().split(',');
                            if(line[0].length === 8 && (/^[\d]+$/g).exec(line[0]) !== null){
                                p.estructura.push({
                                    dni: line[0],
                                    nomape: (line[1]!==undefined?line[1]:''),
                                    email: (line[2]!==undefined?line[2]:''),
                                    movil: (line[3]!==undefined?line[3]:'')
                                });
                            }
                        }
                        // send
                        createReserva(p);
                    };
                    reader.readAsText(file.files[0]);
                } else {
                    createReserva(p);
                }
            } else {
                createReserva(p);
            }
        }

        function createReserva(p){

            if(p.oficina_id === undefined || p.oficina_id <= 0){
                toastr.error("Seleccione una oficina");
                return false;
            }

            if(p.coffeebreak === 'S'){
                p.detalle = [{
                    'concepto': p.selected_cb.id,
                    'precio': p.selected_cb.precio,
                    'cantidad': p.selected_cb.cantidad
                }];
            } else {
                p.detalle = [];
            }

            delete p.selected_cb;

            aux.saving = true;

            if(vm.aux.edit){
                mySrv.update($stateParams.reservaID, p).then(function(r){
                    toastr.success('Datos actualizados');
                    $state.go('reserva');
                }).catch(function(e){
                    toastr.error(e.data.message, 'Error');
                }).finally(function(){
                    vm.aux.saving = false;
                });
            } else {
                mySrv.create(p).then(function(r){
                    console.log(r.data);
                    //var t = null;
                    switch(r.data.reserva.estado) {
                        case 'A':
                            toastr.success('Reserva realizada', null, {timeOut: 5000});
                            break;
                        case 'P':
                            toastr.info('La reserva para el día ' + r.data.reserva.fecha_reserva + ' ha sido registrado. Cuando complete el pago se notificará por correo electrónico. Recuerde que tiene como máximo 5 hora(s) para completar el pago.',null,{timeOut:10000});
                            break;
                        case 'C':
                            toastr.info('Tu reserva ha sido registrada, en breve una de nuestras asesoras se comunicarán para confirmar tu reserva.',null,{timeOut:10000});
                            break;
                    }
                    window.Laravel.reserva = {};
                    //toastr.refreshTimer(t, 10000);
                    $state.go('reserva');
                }).catch(function(e){
                    toastr.error(e.data.message, 'Error');
                }).finally(function(){
                    vm.aux.saving = false;
                });
            }
        }

        function filterHq(){
            aux.locales.splice(1);
            angular.forEach(window.iVariables.locales, function(local){
                if( local.modeloids.split(',').indexOf( params.modelo_id + "" ) >= 0 ){
                    aux.locales.push(local);
                }
            });
        }

        function getCocheras(){
            mySrv.getCocheras(params.reserva_id, {fecha: $filter('date')(params.fecha,'yyyy-MM-dd','America/Lima'), local_id: params.local_id, hini: params.hini, hfin: params.hfin}).then(function(r){
                vm.aux.cocheras.splice(1);
                vm.aux.cocheras = vm.aux.cocheras.concat(r.data);
            });
        }

        function getDisponibilidad(){
            var fecha = $filter('date')(params.fecha,'yyyy-MM-dd','America/Lima');
            mySrv.getAvailable({'fecha': fecha, 'oficina_id': params.oficina_id, 'reserva_id': params.reserva_id}).then(function(r){
                if(r.data.length>0){
                    vm.aux.time_list = [];
                    var date = $filter('date')(new Date(), 'yyyy-MM-dd', 'America/Lima');
                    var time = $filter('date')(new Date(), 'HH:mm:ss', 'America/Lima');
                    r.data.forEach(function(item){
                        if(date === fecha && item.hini <= time){
                            return false;
                        }
                        vm.aux.time_list.push(item);
                    });
                }
            });
        }

        function getTotalCost(){
            aux.diffTime = (params.hfin.substr(0,2)*1) - (params.hini.substr(0,2)*1);
            return (vm.params.selected_cb.cantidad * vm.aux.selected_coffeebreak.precio) + (vm.aux.prices.precio * aux.diffTime) - (vm.aux.selected_cupon.precio);
        }

        function init(){
            if($stateParams.reserva !== undefined){
                // Es una edicion de reserva

                var reserva = $stateParams.reserva;

                //console.log(reserva);

                params.local_id = reserva.local_id;
                params.modelo_id = reserva.modelo_id;
                params.empresa_id = reserva.empresa_id;
                params.fecha = new Date(reserva.fecha_reserva + " 00:00:00");
                params.hini = reserva.hora_inicio;
                params.hfin = reserva.hora_fin;
                params.placa = reserva.placa;
                params.reserva_id = reserva.id;
                aux.selected_company = reserva.empresa_nombre;
                aux.edit = true;

                obtenerDisponibles();

                params.cochera_id = reserva.cochera_id;
                params.oficina_id = reserva.oficina_id;
                params.proyector = reserva.proyector;

                // Auditorio
                if(params.modelo_id != 1){
                    params.nombre = reserva.evento_nombre;
                    params.mesa = reserva.mesa;
                    params.audio = reserva.audio;
                    params.silla = reserva.silla;

                    // Cargar detalle de la reserva (coffeebreak)
                    mySrv.getDetails($stateParams.reservaID).then(function(r){
                        if(r.data.length>0){
                            vm.params.coffeebreak = "S";
                            vm.params.selected_cb = {
                                id: r.data[0].concepto_id,
                                cantidad: parseInt(r.data[0].cantidad),
                                precio: (r.data[0].precio * 1)
                            };

                            vm.aux.selected_coffeebreak = {
                                id: r.data[0].concepto_id,
                                cantidad: parseInt(r.data[0].cantidad),
                                precio: (r.data[0].precio * 1)
                            };
                        }
                    });
                }

                aux.unavailable_space = true;

                getDisponibilidad();

                if(reserva.observacion.length <= 0){
                    params.observacion = "";
                } else {
                    var obs = JSON.parse(reserva.observacion);

                    if(obs.length===0){
                        params.observacion = "";
                    } else {
                        params.observacion = obs[obs.length-1].body;
                    }
                }

            }

            return false;
        }

        function obtenerDisponibles(){

            if(([2,3,5]).indexOf(params.modelo_id)>=0){
                aux.times1 = listSrv.getTimes2();
                aux.times2 = listSrv.getTimes2();

                if(params.hini.substr(3,2) === "30"){
                    params.hini = params.hini.substr(0,2) + ":00:00";
                }

                if(params.hfin.substr(3,2) === "30"){
                    params.hfin = params.hfin.substr(0,2) + ":00:00";
                }

            } else {
                aux.times1 = listSrv.getTimes();
                aux.times2 = listSrv.getTimes();
            }

            if( params.hini === "" || params.hfin === "" ||  params.local_id<=0 || params.modelo_id<=0){
                return false;
            }

            aux.searching_spaces = true;
            aux.oficinas = [];
            aux.space_image = 'preload.jpg';
            aux.time_list = [];
            aux.unavailable_space = false;
            params.oficina_id = 0;
            params.cochera_id = 0;
            params.cupon = "";
            

            mySrv.getAvailableV1({
                local_id: params.local_id,
                modelo_id: params.modelo_id,
                fecha: $filter('date')(params.fecha,'yyyy-MM-dd','America/Lima'),
                hini: params.hini,
                hfin: params.hfin,
                reserva_id: params.reserva_id
            }).then(function(r){
                vm.aux.oficinas = r.data;
            }).catch(function(){
                toastr.error('Hubo un error al obtener las oficinas disponibles');
            }).finally(function(){
                vm.aux.searching_spaces = false;
            });

            // Get cocheras
            getCocheras();

            // Obtener precios de auditorios/salas/terraza
            if(params.local_id>0 && params.modelo_id>0 && params.modelo_id!==1){ 
                mySrv.getPrice(params.local_id, params.modelo_id, 0).then(function(r){
                    vm.aux.prices = r.data;
                });
            }
        }

        function openCalendar(){
            aux.open1 = !aux.open1;
        }

        function selectCoffeebreak(){
            params.selected_cb.id = aux.selected_coffeebreak.id;
            params.selected_cb.precio = aux.selected_coffeebreak.precio;
        }

        function selectOffice(off){
            params.oficina_id = off.oficina_id;
            aux.unavailable_space = (off.reserva_id > 0);
            aux.space_image = off.imagen;

            if(off.reserva_id>0){
                getDisponibilidad();
            }
        }

        function validateCupon(){

            if(aux.blockCupon){
                vm.params.cupon = '';
                vm.aux.blockCupon = false;
                vm.aux.selected_cupon.precio = 0;
            } else {
                aux.searching_cupon = true;
                CuponSrv.validate(vm.params.cupon).then(function(r){
                    vm.aux.blockCupon = true;
                    vm.aux.selected_cupon.precio = (r.data.monto * 1);
                }).catch(function(e){
                    toastr.error(e.data.error, 'Error');
                }).finally(function(){ vm.aux.searching_cupon = false; });
            }
        }
	}
});