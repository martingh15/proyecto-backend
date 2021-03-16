<?php

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/registro", 'UsuarioController@registro');
Route::post("/login", 'LoginController@login');
Route::post("/olvidoPassword", 'LoginController@olvidoPassword');
Route::post("/resetPassword", 'LoginController@resetPassword');
Route::post("/validarToken", 'LoginController@validarToken');
Route::post("/validarTokenEmail", 'LoginController@validarTokenEmail');
Route::resource('/barrios','BarrioController');
Route::resource('/zonas','ZonaController');
Route::get('/perdidos','PerdidoController@index');
Route::get('/encontrados','EncontradoController@index');

Route::group(['middleware' => 'jwt.auth','jwt.refresh'], function () {
    Route::get('/usuarios/create', 'UsuarioController@create');
    Route::put('/usuarios/{id}', 'UsuarioController@update');
    Route::post('/animales','AnimalController@store');
    Route::put('/animales/{idAnimal}','AnimalController@update');
    Route::post('/perdidos','PerdidoController@store');
    Route::post('/update-perdido/{id}','PerdidoController@updatePerdido');
});
