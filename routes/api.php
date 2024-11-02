<?php

use App\Http\Controllers\Authentications\AuthController;
use App\Http\Controllers\Instance\InstanceController;
use App\Http\Controllers\UserManageControllers\UserManageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register',[AuthController::class, 'userRegister']);
Route::post('/logIn',[AuthController::class, 'LogInUser']);
// Route::post('/logOut',[AuthController::class, 'LogOut']);


Route::group([
    'as' => 'passport.',
    'prefix' => 'auth',
    'namespace' => '\Laravel\Passport\Http\Controllers',
], function () {
    Route::post('/logOut',[AuthController::class, 'LogOut']);
    Route::post('/createIotInstance',[InstanceController::class,'Instance']);

});


