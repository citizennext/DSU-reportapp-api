<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return view('welcome', ['version' => $router->app->version()]);
});

// Users routes
$router->group(['prefix' => 'users'], function($router) {

    // <api_url>/users/login
    $router->post('login/', ['as' => 'login.utilizator', 'uses' => 'UserController@autentificare']);

    // <api_url>/users/logout
    $router->post('logout/', 'UserController@deautentificare');

    // <api_url>/users/{id}
    $router->get('{id}', 'UserController@find');

    // <api_url>/users/email/{email}
    $router->get('email/{email}', 'UserController@findByEmail');

    // <api_url>/users
    $router->post('/', 'UserController@create');

    // <api_url>/users/activare/{token}
    $router->get('activare/{token}', ['as' => 'activare.utilizator', 'uses' => 'UserController@activare']);

    // <api_url>/users/edit
    $router->put('edit/', 'UserController@edit');

    // <api_url>/users/{id}
    $router->delete('{id}', 'UserController@delete');

});

// Localitati routes
$router->group(['prefix' => 'localitati'], function($router) {

    // <api_url>/localitati
    $router->get('/', 'LocalitateController@index');

    // <api_url>/localitati/judet/{slug}
    $router->get('/judet/{slug}', 'LocalitateController@localitatiByJudet');

    // <api_url>/localitati/{id}
    $router->get('{id}', 'LocalitateController@find');

    // <api_url>/localitati/slug/{slug}
    $router->get('slug/{slug}', 'LocalitateController@findBySlug');

});