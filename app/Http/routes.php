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

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->post('/auth/logout', [
    'uses' => 'AuthenticationController@logout',
    'middleware' => 'auth'
]);

$app->post('/auth/login', 'AuthenticationController@login');

$app->post('/auth/register', 'AuthenticationController@register');

$app->post('/auth/secure', [
    'uses' =>  'AuthenticationController@secureRoute',
    'middleware' => 'auth'
]);