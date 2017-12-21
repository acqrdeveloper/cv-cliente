<?php
Route::get('/mobile/{funcion}', 'CV\mobileController@funcion');



// Register
Route::get('/registro', function(){ return view('registro', ['locales' => \CVClient\CV\Models\CLocal::where('estado','A')->get(['id','nombre','direccion'])]); });
Route::post('/register', 'CV\empresaController@register');
Route::post('/register/pbx', 'CV\empresaController@registerPbx');
//Route::get('/cupon/valid/{codigo}',	'CV\cuponController@valid');


Route::group(['middleware' => ['auth', 'web']], function () {

    // Pagina Principal
    Route::get('/', function () {
        return view('app');
    })->name('dashboard');

    // WebSocket
    Route::put('/set_ws/{id}', 'Auth\LoginController@setWebSocketId');

    // CERRAR SESION
    Route::get('/salir', 'Auth\LoginController@logout')->name('logout');

    // MODULOS
    foreach (glob(__DIR__ . "/auth/*.php") as $filename) {
        require $filename;
    }
});

Route::group(['middleware' => ['guest']], function () {
    //AUTHENTICACION NORMAL
    Route::get('/login', 'Auth\LoginController@index')->name('login');
    Route::post('/login', 'Auth\LoginController@login')->name('doLogin');

    Route::get('/auth/google', 'Auth\LoginController@redirectToGoogle')->name('google.login');
    Route::get('/auth/google/callback', 'Auth\LoginController@handleCallbackFromGoogle');

    // Facebook Authentication
    Route::get('/auth/facebook', 'Auth\LoginController@redirectToFacebook')->name('facebook.login');
    Route::get('/auth/facebook/callback', 'Auth\LoginController@handleCallbackFromFacebook');
});

Route::get('/images/digicard/{file}', function ($file) {
    if (file_exists(public_path('images/digicard') . '/' . $file)) {
        return response(file_get_contents(public_path('images/digicard') . '/' . $file));
    } else {
        return response()->file(public_path('images') . '/icon.png');
    }
});

/** images **/
Route::prefix('/images')->group(function () {

    $file_404 = public_path() . '/images/preload.jpg';

    Route::get('{type}/{image_file}', function ($type, $image_file) use ($file_404) {

        $file = public_path() . '/images/' . $type . '/' . $image_file;

        if (file_exists(public_path() . '/images/' . $type . '/' . $image_file))
            return response(file_get_contents($file));
        else
            return response(file_get_contents($file_404));
    });

});

// Preview Digicard
Route::get('/digicard', function () {

    $data = (object)[
        "imagen" => "http://alcazar.com.mx/blog/wp-content/uploads/2012/05/emprendedor.jpg",
        "nombre" => "alex quispe",
        "descripcion" => "Profesional Técnico de la carrera de Computación e Informática con experiencias en el campo tecnologicos y desarrollo de software modernos basados en la IA(inteligencia artificial) como sistemas de contacto relacional y sistemas de complementación empresarial.",
        "empresa" => "Somos una empresa de alto indice de productividad y de gran categoria en peru y paises latino americanos y en minimo porcentaje en europa y oriente pero trabajamos por ser lideres mundiales, contamos",
        "productos" => [
            (object)["imagen" => "https://carloscuauhtemoc.com.mx/wp-content/uploads/2017/05/banner-ccs7.jpg",
                "titulo" => "Conference of the Live",
                "descripcion" => "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab adipisci beatae consectetur consequatur dicta illo iste nesciunt nostrum reprehenderit similique! Ad consequatur id nesciunt possimus quidem saepe tempore ullam veniam?",
                "precio" => "550"],
        ],
        "facebook"=>"www.facebook.com.pe"
    ];

    return view("digicard.preview", compact("data"));

});

Route::get('/digicard/{empresa_id}', 'Digicard\masterController@getById');