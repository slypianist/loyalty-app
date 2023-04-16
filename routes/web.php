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
    return response()->json(['app'=>$router->app->version()]);
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('admin/login', 'AuthController@adminLogin');

    $router->post('partner/login', 'AuthController@partnerLogin');

    $router->group(['middleware' => ['auth:admin']], function () use ($router) {
        $router->get('admin/test', 'AuthController@getTest');
        $router->post('admin/logout', 'AuthController@adminLogout');
        $router->get('admin', 'AdminController@index');
        $router->post('admin', 'AdminController@save');
        $router->patch('admin/{id}', 'AdminController@update');
        $router->delete('admin/{id}', 'AdminController@destroy');
    });
        // Partner Routes
    $router->group(['middleware' => ['auth:api']], function () use ($router) {
        $router->get('partner/test', 'AuthController@getTest');
        $router->post('user/logout', 'AuthController@partnerLogout');
    });


});
