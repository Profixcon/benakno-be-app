<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ServicesRequest;
use App\Http\Resources\Master\ServicesResource;
use App\Models\Master\ServicesModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index()
    {
        //get all data from table and order by created_at desc
        $services = ServicesModel::orderBy('created_at', 'desc')->get();

        $list = $services->isEmpty() ? null : ServicesResource::collection($services);

        return response()->success($list, 200, 'All services retrieved successfully');
    }

    public function store(ServicesRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $service = ServicesModel::create($request->validated());

        // Set the default status to 0 after creating the record
        $service->status = 0;
        $service->save();

        return response()->success(new ServicesResource($service), 201, 'Service created successfully');
    }

    public function show(ServicesModel $service, $id)
    {

        try {
            $service = ServicesModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Service not found', 404);
        }

        return response()->success(new ServicesResource($service), 200, 'Service retrieved successfully');
    }

    public function update(ServicesRequest $request, $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            $service = ServicesModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Service not found', 404);
        }

        $service->update($request->validated());
        return response()->success(new ServicesResource($service), 200, 'Service updated successfully');
    }

    public function destroy($id)
    {
        try {
            $service = ServicesModel::findOrFail($id); // Find the record by ID or throw an exception

            //make me code for upload file using filesystem google drive

        } catch (ModelNotFoundException $e) {
            return response()->error('Service not found', 404);
        }

        $service->delete(); // Soft delete
        return response()->success(null, 200, 'Service deleted successfully');
    }
}
