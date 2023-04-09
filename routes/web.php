<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\AdminController;
use Illuminate\Auth\Events\Logout;

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
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('admin/login', 'AdminController@login');

    $router->post('user/login', 'UserController@login');

    $router->group(['middleware' => ['auth:admin']], function () use ($router) {
        $router->get('admin/test', 'AdminController@getTest');
        $router->post('admin/logout', 'AdminController@logout');
    });

    $router->group(['middleware' => ['auth:api']], function () use ($router) {
        $router->get('user/test', 'UserController@getTest');
        $router->post('user/logout', 'UserController@logout');
    });


});
