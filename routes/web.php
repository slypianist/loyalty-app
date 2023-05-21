<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Str;
use Illuminate\Auth\Events\Logout;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoyaltySettingController;
use App\Http\Controllers\ReportController;

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

// Default
$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('admins/login', 'AuthController@adminLogin');
    $router->post('reps/login', 'AuthController@repLogin');
    $router->get('tests', 'TestController@index');
    $router->get('dashboard/card/stats', 'DashboardController@cardStats');
    $router->get('dashboard/bar/stats', 'DashboardController@graphStats');

    $router->post('partners/login', 'AuthController@partnerLogin');
    $router->get('customer/search', 'CustomersController@getCustomerPhoneNum');

        // Reps Routes
    $router->group(['middleware' => ['auth:rep']], function () use ($router) {

        //Award and claim loyalty points.
        $router->post('award/points', 'LoyaltyController@addLoyaltyPoints');
        $router->post('claim/points', 'LoyaltyController@makeClaims');
        $router->post('reps/logout', 'AuthController@repLogout');
        $router->get('auth/rep', 'AuthController@authRep');
});

            // Partner Routes
    $router->group(['middleware' => ['auth:api']], function () use ($router) {
            $router->get('partner/test', 'AuthController@getTest');
            $router->get('auth/partner', 'AuthController@authPartner');
            $router->post('partners/logout', 'AuthController@partnerLogout');
    });


    $router->group(['middleware' => ['auth:admin']], function () use ($router) {
        //Admins
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

        //Reps
        $router->get('reps', 'RepController@index');
        $router->post('rep', 'RepController@createRep');
        $router->get('rep/{id}', 'RepController@showRep');
        $router->patch('rep/{id}', 'RepController@updateRep');
        $router->delete('rep/{id}', 'RepController@destroyRep');

        //Shops
        $router->get('shop', 'ShopsController@index');
        $router->post('shop', 'ShopsController@createShop');
        $router->post('/shop/assign', 'ShopsController@assignShop');
        $router->post('/shop/unassign', 'ShopsController@unassignShop');
        $router->post('/shop/assign/rep', 'ShopsController@assignShopToRep');
        $router->post('/shop/unassign/rep', 'ShopsController@unassignShopToRep');
        $router->get('shop/{id}', 'ShopsController@showShop');
        $router->patch('shop/{id}', 'ShopsController@updateShop');
        $router->delete('shop/{id}', 'ShopsController@destroyShop');

        //Customers
        $router->get('customer', 'CustomersController@index');
        $router->post('customer', 'CustomersController@createCustomer');
        $router->patch('customer/{id}', 'CustomersController@updateCustomer');
        $router->delete('customer/{id}', 'CustomersController@destroyCustomer');
        $router->get('customer/{id}', 'CustomersController@getCustomer');

        // Loyalty Points
        $router->get('rules', 'LoyaltySettingController@index');
        $router->get('rule', 'LoyaltySettingController@getLoyaltyRule');
        $router->post('rule', 'LoyaltySettingController@addLoyaltyRule');
        $router->patch('rule/{id}', 'LoyaltySettingController@updateLoyaltyRule');
        $router->delete('rule/{id}', 'LoyaltySettingController@destroyLoyaltyRule');


        // Permission
        $router->get('permissions', 'PermissionController@getPermission');

        //Roles
        $router->get('role', 'RoleController@index');
        $router->post('role', 'RoleController@store');
        $router->get('role/{id}', 'RoleController@show');
        $router->patch('role/{id}', 'RoleController@update');
        $router->delete('role/{id}', 'RoleController@destroy');

        //Reports
        $router->get('transactions', 'ReportController@getTransactions');
        $router->get('withdrawals', 'ReportController@getClaims');
        $router->get('activity', 'ReportController@getActivities');
        $router->get('top/accruer', 'DashboardController@topAccruer');
        $router->get('top/redeemed', 'DashboardController@topRedeemed');
        $router->get('top/unclaimed', 'DashboardController@topUnclaimed');
        $router->get('top/visit', 'DashboardController@topVisit');
        $router->get('top/center/accrued', 'DashboardController@centerTopAccruer');

    });

});
