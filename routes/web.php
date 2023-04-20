<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Str;
use Illuminate\Auth\Events\Logout;
use App\Http\Controllers\AdminController;

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

$router->get('key', function() use ($router){
    return Str::random(32);

});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('admins/login', 'AuthController@adminLogin');

    $router->post('partners/login', 'AuthController@partnerLogin');

            // Partner Routes
    $router->group(['middleware' => ['auth:api']], function () use ($router) {
            $router->get('partner/test', 'AuthController@getTest');
            $router->post('user/logout', 'AuthController@partnerLogout');
    });


    $router->group(['middleware' => ['auth:admin']], function () use ($router) {
        //Admins
        $router->get('admin/test', 'AuthController@getTest');
        $router->post('admin/logout', 'AuthController@adminLogout');
        $router->get('admin', 'AdminController@index');
        $router->post('admin', 'AdminController@saveAdmin');
        $router->get('admin/{id}', 'AdminController@showAdmin');
        $router->patch('admin/{id}', 'AdminController@updateAdmin');
        $router->delete('admin/{id}', 'AdminController@destroyAdmin');

        //Partners
        $router->get('partner', 'UserController@index');
        $router->post('partner', 'UserController@createPartner');
        $router->get('partner/{id}', 'UserController@showPartner');
        $router->patch('partner/{id}', 'UserController@updatePartner');
        $router->delete('partner/{id}', 'UserController@deletePartner');

        //Customer
        $router->get('customer', 'CustomersController@index');
        $router->post('customer', 'CustomersController@createCustomer');
        $router->get('customer/{id}', 'CustomersController@getCustomer');
        $router->patch('customer/{id}', 'CustomersController@updateCustomer');
        $router->delete('customer/{id}', 'CustomersController@destroyCustomer');
        $router->get('customer/search', 'CustomersController@getCustomerPhoneNum');

        //Shops

        $router->get('shop', 'ShopController@index');
        $router->post('shop', 'ShopController@createShop');
        $router->get('shop/{id}', 'ShopController@showShop');
        $router->patch('shop/{id}', 'ShopController@updateShop');
        $router->delete('shop/{id}', 'ShopController@deleteShop');


    });



});
