<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Chats\ChatController;
use App\Http\Controllers\Master\RatingController;
use App\Http\Controllers\Master\ServicesController;
use App\Http\Controllers\Master\VehicleBrandController;
use App\Http\Controllers\Master\VehicleController;
use App\Http\Controllers\Master\VehicleTypeController;
use App\Http\Controllers\Services\ServiceController;
use App\Http\Controllers\Storage\StorageController;
use App\Http\Controllers\Users\CustomerController;
use App\Http\Controllers\Users\DriverController;
use App\Http\Controllers\Users\MitraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;


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


Route::group(['prefix' => 'v1'], function () {
    Route::get('/linkstorage', function () {
        Artisan::call('storage:link');
    });

    Route::prefix("cache")->group(function () {
        Route::get("/clear-all", function () {
            Artisan::call("optimize:clear");
            Artisan::call("cache:clear");
            Artisan::call("view:clear");
            Artisan::call("route:cache");
            Artisan::call("route:clear");
            Artisan::call("config:cache");
            Artisan::call("config:clear");
            return response()->success(["All has been cleared"]);
        });
        //Clear Cache facade value:
        Route::get("/clear-cache", function () {
            Artisan::call("cache:clear");
            return response()->success(["Cache facade value cleared"]);
        });

        //Reoptimized class loader:
        Route::get("/optimize", function () {
            Artisan::call("optimize");
            return response()->success(["Reoptimized class loader"]);
        });

        //Route cache:
        Route::get("/route-cache", function () {
            Artisan::call("route:cache");
            return response()->success(["Routes cached"]);
        });

        //Clear Route cache:
        Route::get("/route-clear", function () {
            Artisan::call("route:clear");
            return response()->success(["Route cache cleared"]);
        });

        //Clear View cache:
        Route::get("/view-clear", function () {
            Artisan::call("view:clear");
            return response()->success(["View cache cleared"]);
        });

        //Clear Config cache:
        Route::get("/config-cache", function () {
            Artisan::call("config:cache");
            return response()->success(["cache Config cleared"]);
        });

        //Clear Config cache:
        Route::get("/config-clear", function () {
            Artisan::call("config:clear");
            return response()->success(["Clear Config cleared"]);
        });

        Route::get("/dump-load", function () {
            Artisan::call("dump:autoload");
            return response()->success(["DumP Load cleared"]);
        });
    });

    Route::get('/login', [AuthController::class, 'login']);

    Route::group(['prefix' => 'auth'], function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register-customer', [AuthController::class, 'registerCustomer']);
        Route::post('/register-mitra', [AuthController::class, 'registerMitra']);

        Route::post('/refresh-token', [AuthController::class, 'refresh']);

        Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->middleware('jwt.api');
        Route::get('/me', [AuthController::class, 'me'])->middleware('jwt.api');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt.api');
    });

    Route::group(['prefix' => 'users', 'middleware' => 'jwt.api'], function () {

        Route::group(['prefix' => 'customer'], function () {
            Route::get('/', [CustomerController::class, 'index']);
            Route::get('/{user_id}', [CustomerController::class, 'show']);
            Route::post('/{user_id}', [CustomerController::class, 'update']);
            Route::delete('/{user_id}', [CustomerController::class, 'destroy']);
        });

        Route::group(['prefix' => 'mitra'], function () {
            Route::get('/', [MitraController::class, 'index']);
            Route::get('/{user_id}', [MitraController::class, 'show']);
            Route::post('/{user_id}', [MitraController::class, 'update']);
            Route::delete('/{user_id}', [MitraController::class, 'destroy']);
            Route::get('/services/{user_id}', [MitraController::class, 'servicesMitra']);
            Route::put('/services/{user_id}', [MitraController::class, 'updateServicesMitra']);
            Route::put('/operational/{user_id}', [MitraController::class, 'updateOperationalMitra']);
        });

        Route::group(['prefix' => 'driver'], function () {
            Route::get('/', [DriverController::class, 'index']);
            Route::get('/{user_id}', [DriverController::class, 'show']);
            Route::post('/{user_id}', [DriverController::class, 'update']);
            Route::delete('/{user_id}', [DriverController::class, 'destroy']);
        });
    });

    Route::group(['prefix' => 'master'], function () {

        Route::group(['prefix' => 'service'], function () {
            Route::get('/', [ServicesController::class, 'index']);
            Route::get('/{id}', [ServicesController::class, 'show']);
            Route::post('/', [ServicesController::class, 'store'])->middleware('jwt.api');
            Route::put('/{id}', [ServicesController::class, 'update'])->middleware('jwt.api');
            Route::delete('/{id}', [ServicesController::class, 'destroy'])->middleware('jwt.api');
        });
    });

    Route::group(['prefix' => 'master', 'middleware' => 'jwt.api'], function () {

        Route::group(['prefix' => 'vehicle-brand'], function () {
            Route::get('/', [VehicleBrandController::class, 'index']);
            Route::get('/{id}', [VehicleBrandController::class, 'show']);
            Route::post('/', [VehicleBrandController::class, 'store']);
            Route::put('/{id}', [VehicleBrandController::class, 'update']);
            Route::delete('/{id}', [VehicleBrandController::class, 'destroy']);
        });

        Route::group(['prefix' => 'vehicle-type'], function () {
            Route::get('/', [VehicleTypeController::class, 'index']);
            Route::get('/{id}', [VehicleTypeController::class, 'show']);
            Route::post('/', [VehicleTypeController::class, 'store']);
            Route::put('/{id}', [VehicleTypeController::class, 'update']);
            Route::delete('/{id}', [VehicleTypeController::class, 'destroy']);
        });

        Route::group(['prefix' => 'vehicle'], function () {
            Route::get('/', [VehicleController::class, 'index']);
            Route::get('/{id}', [VehicleController::class, 'show']);
            Route::get('/user/{id}', [VehicleController::class, 'showUserVehicle']);
            Route::post('/', [VehicleController::class, 'store']);
            Route::put('/{id}', [VehicleController::class, 'update']);
            Route::delete('/{id}', [VehicleController::class, 'destroy']);
        });

        Route::group(['prefix' => 'rating'], function () {
            Route::get('/', [RatingController::class, 'index']);
            Route::get('/customer/{id}', [RatingController::class, 'showCustomerRating']);
            Route::get('/mitra/{id}', [RatingController::class, 'showMitraRating']);
            Route::get('/{id}', [RatingController::class, 'show']);
            Route::post('/', [RatingController::class, 'store']);
            Route::put('/{id}', [RatingController::class, 'update']);
            Route::delete('/{id}', [RatingController::class, 'destroy']);
        });
    });


    Route::group(['prefix' => 'services', 'middleware' => 'jwt.api'], function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::get('/my-services/{id}', [ServiceController::class, 'myServices']);
        Route::get('/{id}', [ServiceController::class, 'show']);
        Route::post('/', [ServiceController::class, 'store']);
        Route::put('/{id}', [ServiceController::class, 'update']);
        Route::delete('/{id}', [ServiceController::class, 'destroy']);
    });

    Route::group(['prefix' => 'chats', 'middleware' => 'jwt.api'], function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::get('/{id}', [ChatController::class, 'show']);
        Route::get('/history/{id}', [ChatController::class, 'history']);
        Route::post('/', [ChatController::class, 'store']);
        Route::put('/{id}', [ChatController::class, 'update']);
        Route::delete('/{id}', [ChatController::class, 'destroy']);
    });

    Route::group(['prefix' => 'location', 'middleware' => 'jwt.api'], function () {
        Route::group(['prefix' => 'driver'], function () {
            Route::get('/{id}', [\App\Http\Controllers\Location\DriverController::class, 'getDriver']);
            Route::put('/{id}', [\App\Http\Controllers\Location\DriverController::class, 'updateDriver']);
        });
        Route::group(['prefix' => 'customer'], function () {
            Route::get('/{id}', [\App\Http\Controllers\Location\CustomerController::class, 'getCustomer']);
            Route::put('/{id}', [\App\Http\Controllers\Location\CustomerController::class, 'updateCustomer']);
        });
    });

    Route::group(['prefix' => 'reports', 'middleware' => 'jwt.api'], function () {
        Route::get('/services', [ServicesController::class, 'index']);
    });

    Route::group(['prefix' => 'storage'], function () {
        Route::post('/upload', [StorageController::class, 'upload']);
    });

});
