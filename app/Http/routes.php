<?php

/**
 * Authentication Routes
 * 
 * Inlcudes:
 *  login
 *  register
 *  logout
 *  reset password
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

    // reset password for locked out user
    $app->post('resetPassword/{token}', 'AuthenticationController@resetPassword');
});

/**
 * User Account Endpoints
 */

$app->group([ 'prefix' => 'account', 'middleware' => 'auth' ], function () use ($app) {

    // get account info
    $app->get('/', 'UserController@read');

    // update account
    $app->post('update', 'UserController@update');

    // delete account
    $app->delete('delete', 'UserController@delete');

    // change password
    $app->post('changePassword', 'UserController@changePassword');
});