<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ShowController;

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
    // List restaurant by dish
    Route::post('/list-restaurant-by-dish', [ShowController::class, 'listRestaurantByDish']);
});