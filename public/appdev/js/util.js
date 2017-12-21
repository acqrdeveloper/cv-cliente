/**
 * Created by QuispeRoque on 11/04/17.
 */
define(['angular'], function (angular) {

    //UTILIDADES
    var util = {
        rootUrl: location.origin + '/', // account.centrosvirtuales.com - 192.168.1.110:8000
        date: new Date(),
        fillCbo: function () {
            var currentYear = util.date.getUTCFullYear(),
                $years = [],
                months = [
                    {value: 0, name: 'Todos'},
                    {value: 1, name: 'Enero'},
                    {value: 2, name: 'Febrero'},
                    {value: 3, name: 'Marzo'},
                    {value: 4, name: 'Abril'},
                    {value: 5, name: 'Mayo'},
                    {value: 6, name: 'Junio'},
                    {value: 7, name: 'Julio'},
                    {value: 8, name: 'Agosto'},
                    {value: 9, name: 'Septiembre'},
                    {value: 10, name: 'Octubre'},
                    {value: 11, name: 'Noviembre'},
                    {value: 12, name: 'Diciembre'}
                ],
                typereports = [
                    {id: 1, name: 'Correspondencia'},
                    {id: 2, name: 'Llamadas'},
                    {id: 3, name: 'Pagos'},
                    {id: 4, name: 'Comprobantes Pagados'},
                    {id: 5, name: 'Saldos y Excedentes'},
                    {id: 6, name: 'Cuadres'},
                    {id: 7, name: 'Garantias'},
                ];
            for (var i = 2012; i <= currentYear; i++) {
                $years.push({name: i, id: i});
            }
            return {
                years: $years,
                selectedYear: {id: currentYear, name: currentYear},
                months: months,
                selectedMonth: {value: util.date.getMonth() + 1},
                reports: typereports,
                selectedReport: {id: 1, name: 'correspondencia'}
            };
        },
        errorRpta: function (r) {
            toastr.error('ERROR',r.data.error.detail);
        },
        getElement: function (type) {
            if (type !== null) {
                if (angular.element(document.getElementById(type)).length > 0) {
                    return angular.element(document.getElementById(type));
                } else if (angular.element(document.getElementsByClassName(type)).length > 0) {
                    return angular.element(document.getElementsByClassName(type));
                }
            }
        },
        fnGetObjectFields: function (fields, other) {
            var data = {}, otherField = [];
            angular.forEach(fields,function (val) {
                if (parseInt(val.value) && !isNaN(val.value)) {
                    data[val.name] = parseFloat(val.value);
                } else if (val.name !== 'id') {
                    data[val.name] = val.value;
                }
                if (val.name === other) {
                    otherField.push(val.value);
                }
            });
            // _.each(fields, function (val) {
            //     if (parseInt(val.value) && !isNaN(val.value) && val.name != 'fac_dni' && val.name != 'dni' && val.name != 'empresa_ruc') {
            //         data[val.name] = parseFloat(val.value);
            //     } else if (val.name !== 'id') {
            //         data[val.name] = val.value;
            //     }
            //     if (val.name === other) {
            //         otherField.push(val.value);
            //     }
            // });

            if (otherField.length) {
                data[other] = otherField;
            }
            return data;
        }
    };

    return util;
});
