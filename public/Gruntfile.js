module.exports = function(grunt) {
    grunt.initConfig({
        cssmin: {
            combine: {
                dest: 'css/app.min.css',
                src: [
                    'appdev/css/skin/default_skin/css/theme.min.css',
                    'appdev/css/admin-tools/admin-forms/css/admin-forms.min.css',
                    'node_modules/ui-select/dist/select.css',
                    'node_modules/angular-toastr/dist/angular-toastr.css',
                    'appdev/css/app.css',
                ]
            }
        },
        requirejs: {
            compile: {
                options: {
                    almond: true,
                    baseUrl: ".",
                    out: 'js/app.min.js',
                    name: 'main',
                    mainConfigFile: 'main.js',
                    include: ['node_modules/requirejs/require'],
                    preserveLicenseComments: false
                }
            }
        },
        jshint: {
            all: ['Gruntfile.js', 'appdev/js/**/*.js', 'main.js']
        }    
    });

    //Cargamos las tareas
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-jshint');

    //Registramos las tareas
    grunt.registerTask('minify', ['cssmin']);
    grunt.registerTask('jscheck', ['jshint']);
    grunt.registerTask('jsapp', ['jshint', 'requirejs']);
};