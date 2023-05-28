<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Str;
use Illuminate\Auth\Events\Logout;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoyaltySettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PasswordResetController;

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
    //Open routes
    $router->get('rule', 'LoyaltySettingController@getLoyaltyRule');
    $router->get('customer/search', 'CustomersController@getCustomerPhoneNum');

    // Password Reset Users.
    $router->post('send/link/partner', 'PasswordResetController@sendResetLinkPartner');
    $router->post('send/link/rep', 'PasswordResetController@sendResetLinkRep');
    $router->post('send/link/admin', 'PasswordResetController@sendResetLinkAdmin');

    $router->post('reset/partner/pw', 'PasswordResetController@resetPartnerPassword');
    $router->post('reset/rep/pw', 'PasswordResetController@resetRepPassword');
    $router->post('reset/admin/pw', 'PasswordResetController@resetAdminPassword');


    $router->post('partners/login', 'AuthController@partnerLogin');
    $router->get('customer/search', 'CustomersController@getCustomerPhoneNum');
    $router->get('check/invoice', 'InvoiceController@checkInvoice');

        // Reps Routes
    $router->group(['middleware' => ['auth:rep']], function () use ($router) {

        //Award and claim loyalty points.
        $router->post('award/points', 'LoyaltyController@addLoyaltyPoints');
        $router->post('claim/points', 'LoyaltyController@makeClaims');
        $router->post('reps/logout', 'AuthController@repLogout');
        $router->get('auth/rep', 'AuthController@authRep');
        $router->get('dashboard/rep/card/stats', 'RepDashboardController@cardStats');
});
            // Partner Routes
    $router->group(['middleware' => ['auth:api']], function () use ($router) {
            $router->get('partner/test', 'AuthController@getTest');
            $router->get('auth/partner', 'AuthController@authPartner');
            $router->post('partners/logout', 'AuthController@partnerLogout');
            $router->get('dashboard/partner/card/all', 'DashboardController@partnerCardStatsAll');
            $router->get('dashboard/partner/card', 'DashboardController@partnerCardStats');
            $router->get('dashboard/partner/bar', 'DashboardController@partnerBarStats');
            $router->get('dashboard/partner/center', 'DashboardController@getPartnerCenter');
            $router->get('dashboard/partner/top/accrued', 'DashboardController@sideBarStats1');
            $router->get('dashboard/partner/top/claimed', 'DashboardController@sideBarStats2');
            $router->get('dashboard/partner/top/visit', 'DashboardController@sideBarStats3');
    });

    $router->group(['middleware' => ['auth:admin']], function () use ($router) {
        //Admins
        $router->post('admin/logout', 'AuthController@adminLogout');
        $router->get('admin', 'AdminController@index');
        $router->post('admin', 'AdminController@saveAdmin');
        $router->get('admin/{id}', 'AdminController@showAdmin');
        $router->patch('admin/{id}', 'AdminController@updateAdmin');
        $router->delete('admin/{id}', 'AdminController@destroyAdmin');
        $router->get('dashboard/card/stats', 'AdminDashboardController@cardStats');
        $router->get('dashboard/bar/stats', 'AdminDashboardController@graphStats');

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
        $router->get('top/accruer', 'AdminDashboardController@topAccruer');
        $router->get('top/redeemed', 'AdminDashboardController@topRedeemed');
        $router->get('top/unclaimed', 'AdminDashboardController@topUnclaimed');
        $router->get('top/visit', 'AdminDashboardController@topVisit');
        $router->get('top/center/accrued', 'AdminDashboardController@centerTopAccruer');

    });



});
