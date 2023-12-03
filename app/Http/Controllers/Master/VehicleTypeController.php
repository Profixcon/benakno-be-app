<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\VehicleTypeRequest;
use App\Http\Resources\Master\VehicleTypeResource;
use App\Models\Master\VehicleTypeModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index()
    {
        $vehicleTypes = VehicleTypeModel::all();

        $list = $vehicleTypes->isEmpty() ? null : VehicleTypeResource::collection($vehicleTypes);

        return response()->success($list, 200, 'All Type retrieved successfully');
    }

    public function store(VehicleTypeRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $vehicleType = VehicleTypeModel::create($request->validated());

        return response()->success(new VehicleTypeResource($vehicleType), 201, 'Vehicle Type created successfully');
    }

    public function show(VehicleTypeModel $vehicleType, $id)
    {

        try {
            $vehicleType = VehicleTypeModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Vehicle Type not found', 404);
        }

        return response()->success(new VehicleTypeResource($vehicleType), 200, 'Vehicle Type retrieved successfully');
    }

    public function update(VehicleTypeRequest $request, $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            $vehicleType = VehicleTypeModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Vehicle Type not found', 404);
        }

        $vehicleType->update($request->validated());
        return response()->success(new VehicleTypeResource($vehicleType), 200, 'Vehicle Type updated successfully');
    }

    public function destroy($id)
    {
        try {
            $vehicleType = VehicleTypeModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Vehicle Type not found', 404);
        }

        $vehicleType->delete(); // Soft delete
        return response()->success(null, 200, 'Vehicle Type deleted successfully');
    }
}
