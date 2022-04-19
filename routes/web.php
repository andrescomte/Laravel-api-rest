<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//Cargado clases
use \App\Http\Middleware\ApiAuthMiddleware;

// Route::get('/welcome', function () {
//     return view('welcome');
// });

// Route::get('/', function(){
//    return '<h1>probandooooo </h1>';
// });

/*Route::get('/prueba/{nombre?}', function($nombre = null) {
    $texto = '<h2>Texto desde una ruta</h2>';
    $texto .= 'Nombre: '.$nombre; #el .= es para concatenar
    #return $texto;
    #en vez de imprimir dentro de la route se imprime en la vista, es una buena practica
    return view('pruebas', array(
        'texto' => $texto
    ));
});
*/

/*-----Metodos HHTP comunes
 * GET : Conseguir datos o recursos
 * POST : guardar datos o recursos hacer logica(formulario)
 * PUT: actualizar recursos o datos del backend
 * DELETE : eliminar datos o recursos
 */

//Route::get('/animales', 'PruebasController@index');
//Route::get('/test-orm', 'PruebasController@testOrm');

//rutas de prueba
//Route::get('/pruebauser', 'UserController@pruebas');
//Route::get('/pruebapost', 'PostController@pruebas');
//Route::get('/entrada/pruebacatego', 'CategoryController@pruebas');

//Rutas de la api
Route::post('/api/login', 'UserController@login');
Route::post('/api/register', 'UserController@register');
Route::put('/api/user/update', 'UserController@update');
Route::post('/api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
Route::get('/api/user/detail/{id}', 'UserController@detail');


//Rutas del controlador de categorias.
Route::resource('/api/category', 'CategoryController'); // rutas de tipo resource ... con esta llamada nos crea todas las rutas, con sus distitnos nombres

                                                        //esto se puede apreciar en la carpeta api-rest con el codigo 'php artisan route:list'
//Rutas del controlados de Post
Route::resource('/api/post', 'PostController');
Route::post('/api/post/upload', 'PostController@upload');
Route::get('api/post/image/{filename}','PostController@getImage');
Route::get('api/post/category/{id}','PostController@getPostsByCategory');
Route::get('api/post/user/{id}','PostController@getPostByUser');
