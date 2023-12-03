<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\VehicleBrandRequest;
use App\Http\Resources\Master\VehicleBrandResource;
use App\Models\Master\VehicleBrandModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class VehicleBrandController extends Controller
{
    public function index()
    {
        $vehicleBrands = VehicleBrandModel::all();

        $list = $vehicleBrands->isEmpty() ? null : VehicleBrandResource::collection($vehicleBrands);

        return response()->success($list, 200, 'All Brand retrieved successfully');
    }

    public function store(VehicleBrandRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $vehicleBrand = VehicleBrandModel::create($request->validated());

        return response()->success(new VehicleBrandResource($vehicleBrand), 201, 'Vehicle Brand created successfully');
    }

    public function show(VehicleBrandModel $vehicleBrand, $id)
    {

        try {
            $vehicleBrand = VehicleBrandModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Vehicle Brand not found', 404);
        }

        return response()->success(new VehicleBrandResource($vehicleBrand), 200, 'Vehicle Brand retrieved successfully');
    }

    public function update(VehicleBrandRequest $request, $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            $vehicleBrand = VehicleBrandModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Vehicle Brand not found', 404);
        }

        $vehicleBrand->update($request->validated());
        return response()->success(new VehicleBrandResource($vehicleBrand), 200, 'Vehicle Brand updated successfully');
    }

    public function destroy($id)
    {
        try {
            $vehicleBrand = VehicleBrandModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Vehicle Brand not found', 404);
        }

        $vehicleBrand->delete(); // Soft delete
        return response()->success(null, 200, 'Vehicle Brand deleted successfully');
    }
    //
}
