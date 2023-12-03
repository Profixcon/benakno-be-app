<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Http\Requests\Location\CustomerRequest;
use App\Http\Requests\Location\DriverRequest;
use App\Http\Resources\Location\CustomerResource;
use App\Http\Resources\Location\DriverResource;
use App\Models\Location\CustomerModel;
use App\Models\Location\DriverModel;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    //get driver latitude and longitude by user id
    public function getDriver($id)
    {
        //check if driver exist
        $driver = DriverModel::where('user_id', $id)->first();
        if ($driver) {

            // check if lat and long is null
            if ($driver->latitude == null && $driver->longitude == null) {
                return response()->error('Driver location not found', 404);
            }

            return response()->success(new DriverResource($driver), 200, 'Succcessfully get driver location');
        } else {
            return response()->error('Driver not found', 404);
        }
    }

    //update driver location
    public function updateDriver(DriverRequest $request, $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        //check if driver exist
        $driver = DriverModel::where('user_id', $id)->first();
        if ($driver) {
            $driver->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            $driver = DriverModel::where('user_id', $id)->first();

            return response()->success(new DriverResource($driver), 200, 'Succcessfully update driver location');
        } else {
            return response()->error('Driver not found', 404);
        }
    }
}
