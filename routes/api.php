<?php

use App\Http\Controllers\Authentications\AuthController;
use App\Http\Controllers\Climate\ClimateController;
use App\Http\Controllers\Instance\InstanceController;
use App\Http\Controllers\mq2\AirConditionStatusController;
use App\Http\Controllers\Objects\ObjectController;
use App\Http\Controllers\RabbitMq\PassToQController;
use App\Http\Controllers\UserManageControllers\UserManageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Passport::routes();
Route::post('/Register',[AuthController::class, 'userRegister']);
Route::post('/login',[AuthController::class, 'LogInUser'])->name('login');
// Route::post('/logOut',[AuthController::class, 'LogOut']);

// Passport::routes();
Route::group([
    'as' => 'passport.',
    'prefix' => 'auth',
    'middleware' => ['auth:api'],
    'namespace' => '\Laravel\Passport\Http\Controllers',
], function () {
    Route::post('/LogOut',[AuthController::class, 'LogOut']);
    Route::post('/CreateIotInstance',[InstanceController::class,'Instance']);
    //get instance details
    Route::get('/InstanceDetails',[ObjectController::class,'GetInstances']);
    //save new sensor
    Route::post('/SaveSensor',[ObjectController::class,'AddNewSensor']);
    //update user profile
    // Route::put('/updateUsers',[UserManageController::class,'updateUserProfile']);
    Route::put('/updateUsers',[UserManageController::class,'updateUserProfile'])->middleware('scope:read-profile');
    Route::put('/userAccountsActivate',[UserManageController::class,'activeUser']);
    Route::put('/userAccountsDeactivate',[UserManageController::class,'deactivateUser']);

    //pass data to rabbitMq start
    //light on
    Route::get('/turnOnLight',[PassToQController::class,'lightOn']);
    //light off
    Route::get('/turnOffLight',[PassToQController::class,'lightOff']);
    Route::post('/receiveSensorData',[PassToQController::class,'receiveSensorData']);

    //pass data to rabbitMq end


    //read temperature values
    Route::post('/readTemperature',[ClimateController::class,'temperature']);
    //read humidity values
    Route::post('/readHumidity',[ClimateController::class,'humidity']);
    //read both temperature and humidity
    Route::post('/readTemHumidity',[ClimateController::class,'bothTemHumidity']);

    //get sensor data for testing purpose
    Route::get('/sensorData',[ClimateController::class,'index']);
    //air quality
    Route::get('/mq2AirQuality',[AirConditionStatusController::class,'mq2Co2']);



})->middleware('auth:api');

// Route::put('/updateUsers', [UserManageController::class, 'updateUserProfile'])
//      ->middleware('auth:api', 'scope:read-profile');


// Route::put('/updateUsers', [UserManageController::class, 'updateUserProfile'])
//      ->middleware('auth:api', 'scope:read-profile');

// Route::middleware('auth:api')->group(function () {
//     Route::post('/updateUsers', [UserManageController::class, 'updateUserProfile'])->middleware('scope:read-profile');
//     Route::get('/read-only', [AuthController::class, 'readOnlyAccess'])->middleware('scope:read-only,admin');
// });
