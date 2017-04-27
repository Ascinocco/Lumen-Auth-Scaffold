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
    // you will need a view to upload the pw reset info
    // the controller function to load the view has not been stubbed out as I want to use
    // this scaffold with SPA's'
    $app->post('resetPassword/{token}', 'AuthenticationController@resetPassword');
    $app->post('requestPasswordReset', 'AuthenticationController@requestPasswordReset');
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