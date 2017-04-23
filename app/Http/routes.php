<?php

/**
 * Authentication Routes
 * 
 * Inlcudes:
 *  login
 *  register
 *  logout
 */
$app->group([ 'prefix' => 'auth' ], function () use ($app) {

    // login
    $app->post('login', 'AuthenticationController@login');

    // register
    $app->post('register', 'AuthenticationController@register');

    // logout
    $app->post('logout', [
        'uses' => 'AuthenticationController@logout',
        'middleware' => 'auth'
    ]);

});

/**
 * User Account Endpoints
 */

$app->group([ 'prefix' => 'account', 'middleware' => 'auth' ], function () use ($app) {

    // get account info
    $app->get('/', 'UserController@read');

    // update
    $app->post('update', 'UserController@update');
});