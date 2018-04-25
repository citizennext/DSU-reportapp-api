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

    // <api_url>/utilizator/logare
    $router->post('logare/', ['as' => 'login.utilizator', 'uses' => 'UserController@autentificare']);

    // <api_url>/utilizator/delogare
    $router->post('delogare/', 'UserController@deautentificare');

    // <api_url>/utilizator/{id}
    $router->get('{id}', 'UserController@find');

    // <api_url>/utilizator/email/{email}
    $router->get('email/{email}', 'UserController@findByEmail');

    // <api_url>/utilizator/adaugare
    $router->post('adaugare/', 'UserController@create');

    // <api_url>/utilizator/activare/{token}
    $router->get('activare/{token}', ['as' => 'activare.utilizator', 'uses' => 'UserController@activare']);

    // <api_url>/utilizator/editare
    $router->put('editare/', 'UserController@edit');

    // <api_url>/utilizator/stergere/{id}
    $router->delete('stergere/{id}', 'UserController@delete');

    // <api_url>/utilizator/resetare-parola
    $router->put('resetare-parola/', 'UserController@resetPassword');

});

// Localitati routes
$router->group(['prefix' => 'localitati'], function($router) {

    // <api_url>/localitati
    $router->get('/', 'LocalitateController@index');

    // <api_url>/localitati/judet/{slug}
    $router->get('/judet/{slug}', 'LocalitateController@localitatiByJudet');

});
$router->group(['prefix' => 'localitate'], function($router) {

    // <api_url>/localitate/{id}
    $router->get('{id}', 'LocalitateController@find');

    // <api_url>/localitate/slug/{slug}
    $router->get('slug/{slug}', 'LocalitateController@findBySlug');

    // <api_url>/localitate/adaugare
    $router->post('adaugare/', 'LocalitateController@create');

    // <api_url>/localitate/editare
    $router->put('editare/', 'LocalitateController@edit');

    // <api_url>/localitate/stergere/{id}
    $router->delete('stergere/{id}', 'LocalitateController@delete');
});

// Judete routes
$router->group(['prefix' => 'judete'], function($router) {

    // <api_url>/judete
    $router->get('/', 'JudetController@index');

});
$router->group(['prefix' => 'judet'], function($router) {

    // <api_url>/judet/{id}
    $router->get('{id}', 'JudetController@find');

    // <api_url>/judet/slug/{slug}
    $router->get('slug/{slug}', 'JudetController@findBySlug');

    // <api_url>/judet/adaugare
    $router->post('adaugare/', 'JudetController@create');

    // <api_url>/judet/editare
    $router->put('editare/', 'JudetController@edit');

    // <api_url>/judet/stergere/{id}
    $router->delete('stergere/{id}', 'JudetController@delete');
});

// Unitati routes
$router->group(['prefix' => 'unitati'], function($router) {

    // <api_url>/unitati
    $router->get('/', 'UnitateController@index');

    // <api_url>/unitati/parinte/{id}
    $router->get('parinte/{id}', 'UnitateController@unitatiByParent');

    // <api_url>/unitati/departament/{id}
    $router->get('departament/{id}', 'UnitateController@unitatiByDepartament');

});
$router->group(['prefix' => 'unitate'], function($router) {

    // <api_url>/unitate/{id}
    $router->get('{id}', 'UnitateController@find');

    // <api_url>/unitate/adaugare
    $router->post('adaugare/', 'UnitateController@create');

    // <api_url>/unitate/editare
    $router->put('editare/', 'UnitateController@edit');

    // <api_url>/unitate/stergere/{id}
    $router->delete('stergere/{id}', 'UnitateController@delete');
});

// Departamente routes
$router->group(['prefix' => 'departamente'], function($router) {

    // <api_url>/departamente
    $router->get('/', 'DepartamentController@index');

});
$router->group(['prefix' => 'departament'], function($router) {

    // <api_url>/departament/{id}
    $router->get('{id}', 'DepartamentController@find');

    // <api_url>/departament/adaugare
    $router->post('adaugare/', 'DepartamentController@create');

    // <api_url>/departament/editare
    $router->put('editare/', 'DepartamentController@edit');

    // <api_url>/departament/stergere/{id}
    $router->delete('stergere/{id}', 'DepartamentController@delete');
});

// Roluri si Permisiuni routes
$router->group(['prefix' => 'roluri'], function($router) {

    // <api_url>/roluri
    $router->get('/', 'RoleController@index');

});
$router->group(['prefix' => 'rol'], function($router) {

    // <api_url>/rol/{id}
    $router->get('{id}', 'RoleController@find');

    // <api_url>/rol/adaugare
    $router->post('adaugare/', 'RoleController@create');

    // <api_url>/rol/editare
    $router->put('editare/', 'RoleController@edit');

    // <api_url>/rol/stergere/{id}
    $router->delete('stergere/{id}', 'RoleController@delete');
});