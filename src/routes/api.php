<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ShowController;
use App\Http\Controllers\PurchaseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    // Authentication
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);   

    // Import Data
    Route::post('/import-restaurant', [ImportController::class, 'importJsonRestaurant']);
    Route::post('/import-user', [ImportController::class, 'importJsonUser']);

    // List restaurant by datetime
    Route::post('/list-restaurant-by-datetime', [ShowController::class, 'listRestaurantByDatetime']);
    // List all restaurants within the vicinity of the user’s location or (any location), ranked by distance
    Route::post('/list-restaurant-by-distance', [ShowController::class, 'listRestaurantByDistance']);
    // List all restaurants that are open for x-z hours per day or week
    Route::post('/list-restaurant-by-open-hours', [ShowController::class, 'listRestaurantByOpenHours']);
    // List restaurant by price menu
    Route::post('/list-restaurant-by-price', [ShowController::class, 'listRestaurantByPrice']);
    // List restaurant or dish by search
    Route::post('/list-restaurant-dish', [ShowController::class, 'listRestaurantDish']);
    // List restaurant by dish
    Route::post('/list-restaurant-by-dish', [ShowController::class, 'listRestaurantByDish']);
    // List users by transaction
    Route::post('/list-user-by-transaction', [ShowController::class, 'listUserByTransaction']);
    // List restaurant by number transaction or transaction amount
    Route::post('/list-restaurant-by-transaction', [ShowController::class, 'listRestaurantByTransaction']);
    // Total number of users by above or below transaction amount with daterange
    Route::post('/total-user-by-transaction-amount', [ShowController::class, 'totalUserByTransactionAmount']);
    // List all transaction belonging to a restaurant or user
    Route::post('/list-transaction', [ShowController::class, 'listTransaction']);
    // Check balances
    Route::post('/check-balances', [ShowController::class, 'checkBalances']);
    // purchase order
    Route::post('/purchase-order', [PurchaseController::class, 'purchaseOrder']);
});