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

// Domains routes
$router->group(['prefix' => 'localitati'], function($router) {

    // <api_url>/localitati
    $router->get('/', 'LocalitateController@index');

});