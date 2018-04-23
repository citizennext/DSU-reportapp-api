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
$router->group(['prefix' => 'utilizator'], function($router) {

    // <api_url>/users/logare
    $router->post('logare/', ['as' => 'login.utilizator', 'uses' => 'UserController@autentificare']);

    // <api_url>/users/delogare
    $router->post('delogare/', 'UserController@deautentificare');

    // <api_url>/users/{id}
    $router->get('{id}', 'UserController@find');

    // <api_url>/users/email/{email}
    $router->get('email/{email}', 'UserController@findByEmail');

    // <api_url>/users/adaugare
    $router->post('adaugare/', 'UserController@create');

    // <api_url>/users/activare/{token}
    $router->get('activare/{token}', ['as' => 'activare.utilizator', 'uses' => 'UserController@activare']);

    // <api_url>/users/editare
    $router->put('editare/', 'UserController@edit');

    // <api_url>/users/stergere/{id}
    $router->delete('stergere/{id}', 'UserController@delete');

    // <api_url>/users/resetare-parola
    $router->put('resetare-parola/', 'UserController@resetPassword');

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

    // <api_url>/localitati/adaugare
    $router->post('adaugare/', 'LocalitateController@create');

    // <api_url>/localitati/editare
    $router->put('editare/', 'LocalitateController@edit');

    // <api_url>/localitati/stergere/{id}
    $router->delete('stergere/{id}', 'LocalitateController@delete');
});

// Judete routes
$router->group(['prefix' => 'judete'], function($router) {

    // <api_url>/judete
    $router->get('/', 'JudetController@index');

    // <api_url>/judete/{id}
    $router->get('{id}', 'JudetController@find');

    // <api_url>/judete/slug/{slug}
    $router->get('slug/{slug}', 'JudetController@findBySlug');

    // <api_url>/judete/adaugare
    $router->post('adaugare/', 'JudetController@create');

    // <api_url>/judete/editare
    $router->put('editare/', 'JudetController@edit');

    // <api_url>/judete/stergere/{id}
    $router->delete('stergere/{id}', 'JudetController@delete');
});