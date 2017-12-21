define(['angular','app'], function (angular, app) {
    return app.config(['$urlRouterProvider', '$locationProvider', '$stateProvider', function ($urlRouterProvider, $locationProvider, $stateProvider) {
        $stateProvider
            .state('app', {
                abstract: true,
                templateUrl: '/views/full.html'
            })
            .state('dashboard', {
                url: '/',
                templateUrl: '/views/dashboard.html',
                parent: 'app',
                controller:'DashboardCtrl',
                controllerAs: 'vm'
            })
            .state('profile', {
                url: '/profile',
                templateUrl: '/views/profile.html',
                parent: 'app',
                controller:'ProfileCtrl',
                controllerAs: 'vm'
            })
            .state('reserva', {
                url: '/reserva',
                templateUrl: '/views/reserva.html',
                parent: 'app',
                controller:'ReservaCtrl',
                controllerAs: 'vm'
            })
            .state('reserva-create', {
                url: '/reserva/create',
                templateUrl: '/views/create-reserva.html',
                parent: 'app'
            })
            .state('bandeja', {
                url: '/bandeja',
                templateUrl: '/views/bandeja.html',
                parent: 'app',
                controller:'BandejaCtrl',
                controllerAs: 'vm'
            })
            .state('reporte', {
                url: '/reporte',
                templateUrl: '/views/reporte.html',
                parent: 'app',
                controller:'ReporteCtrl',
                controllerAs: 'vm'
            })
            .state('abogado', {
                url: '/abogado',
                templateUrl: '/views/abogado.html',
                parent: 'app',
                controller:'AbogadoCtrl',
                controllerAs: 'vm'
            });

        $locationProvider.html5Mode({
            enabled: true,
            requireBase: false
        });
    }]).run(['$rootScope', '$state', '$http', '$timeout', function ($rootScope, $state, $http, $timeout) {
        $rootScope.$state = $state;

        Object.assign({
            'Authorization': 'Bearer ' + window.Laravel.at,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }, $http.defaults.headers.common);
    }]);
});