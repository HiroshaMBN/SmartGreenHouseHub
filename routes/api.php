<?php

use App\Http\Controllers\Authentications\AuthController;
use App\Http\Controllers\Climate\ClimateController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Instance\InstanceController;
use App\Http\Controllers\mq2\AirConditionStatusController;
use App\Http\Controllers\Notification\notificationController;
use App\Http\Controllers\Objects\ObjectController;
use App\Http\Controllers\RabbitMq\PassToQController;
use App\Http\Controllers\RabbitMq\PublishToMessageToNodemcu;
use App\Http\Controllers\RabbitMq\QueueNexchangeController;
use App\Http\Controllers\RabbitMq\RabbitMqConfiguration;
use App\Http\Controllers\SoilMoisture\SoilLevelController;
use App\Http\Controllers\Stocks\StocksController;
use App\Http\Controllers\Thresholds\thresholdsController;
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

    //highest humidity in a month
    Route::post('/maxHumidity',[ClimateController::class,'highestNumberOFHumidityRecord']);
    // 

    //get sensor data for testing purpose
    Route::get('/sensorData',[ClimateController::class,'index']);
    //air quality
    Route::post('/mq2AirQuality',[AirConditionStatusController::class,'mq2Co2']);

     //RabbitMq intergration
    //list vhost
    Route::get('/getVhosts',[RabbitMqConfiguration::class,'ListVHosts']);
    //make vhost
    Route::post('/makeHost',[RabbitMqConfiguration::class,'MakeVHost']);
    //delete v host
    Route::post('/deleteVHost',[RabbitMqConfiguration::class,'DeleteVhost']);
    //get rabbitmq users
    Route::get('/getRabbitMqUsers',[RabbitMqConfiguration::class,'UserList']);
    //creata a rabbitmq user with password
    Route::post('/createAUser',[RabbitMqConfiguration::class,'CreateUser']);
    //add Tags
    Route::post('/addTags',[RabbitMqConfiguration::class,'AddTags']);
    //can access virtual host
    Route::post('/accessVhost',[RabbitMqConfiguration::class,'CanAccessVirtualHosts']);
    //*restart rabbitmq
    Route::get('/restartRabbitMq',[RabbitMqConfiguration::class,'RestartRabbitMq']);
    //create a exchnage
    Route::post('/createExchange',[QueueNexchangeController::class,'MakeExchange']);
    //create a queue
    Route::post('/createQueue',[QueueNexchangeController::class,'MakeQueue']);
    //delete exchange
    Route::post('/deleteExchange',[QueueNexchangeController::class,'DeleteExchange']);

    //soil moisture sensor data
    Route::post('/soilLevel',[SoilLevelController::class,'SoilMoistureLevel']);
    
    //rabbitmq overview
    Route::get('/rabbitmq_overview',[RabbitMqConfiguration::class,'overView']);
    //rabbitmq connection list
    // 
    Route::get('/rabbitmq_connections',[RabbitMqConfiguration::class,'showConnection']);



     //Normal delete
     Route::post('/deleteQueue',[QueueNexchangeController::class,'DeleteQueue']);
     //delete queue force method request
     Route::get('/ReqForceDeleteQueue', [QueueNexchangeController::class, 'RequestForceDeleteQueue']);
     // delete queue force confirmed
     Route::post('/confirmedDeleteQueue',[QueueNexchangeController::class,'QDeleteConfirmed']);
     //delete queue if empty
     // Route::post('/deleteQueue', [QueueMakerController::class, 'ConfirmForceDeleteQueue']);
    //add sensor
    Route::post('/add_senors',[thresholdsController::class,'AddSensors']);
    //get sensor names
    Route::get('/sensor_name',[thresholdsController::class,'getSensors']);
     //add thresholds valuee
    Route::post('/SetThresholds',[thresholdsController::class,'SensorThresholds']);

    //get temperature thresholds values
    Route::get('/temperature_threshold',[thresholdsController::class,'temperatureThreshold']);

    //notification
    Route::get('/tmp_alert',[notificationController::class,'temperatureAlert']);
    //enable whole notification at once 
    Route::post('/enable_notifications',[notificationController::class,'enableNotifications']);
    //get register's users list for the set contact
    Route::get('reg_users' ,[ContactController::class,'userList']);
    //update contact table
    Route::post('/update_contact',[ContactController::class,'addUserContact']);

    //stocks
    Route::post('/add_fertilization_stocks',[StocksController::class,'fertilizationStocks']);
    Route::post('/add_seeds_stocks',[StocksController::class,'seedStocks']);

    //publish message
    Route::post('/publish_on_off_light_one',[PublishToMessageToNodemcu::class,'lightOne']);
    Route::post('/publish_on_off_light_two',[PublishToMessageToNodemcu::class,'lightTwo']);
    Route::post('/publish_on_off_exhaust_fan',[PublishToMessageToNodemcu::class,'exhaustFan']);

    //run command in terminal

    Route::get('/terminal',[RabbitMqConfiguration::class,'Terminal']);

})->middleware('auth:api');

// Route::put('/updateUsers', [UserManageController::class, 'updateUserProfile'])
//      ->middleware('auth:api', 'scope:read-profile');


// Route::put('/updateUsers', [UserManageController::class, 'updateUserProfile'])
//      ->middleware('auth:api', 'scope:read-profile');

// Route::middleware('auth:api')->group(function () {
//     Route::post('/updateUsers', [UserManageController::class, 'updateUserProfile'])->middleware('scope:read-profile');
//     Route::get('/read-only', [AuthController::class, 'readOnlyAccess'])->middleware('scope:read-only,admin');
// });
