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