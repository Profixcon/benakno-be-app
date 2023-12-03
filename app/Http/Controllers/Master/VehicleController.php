<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ListDataRequest;
use App\Http\Requests\Master\VehicleRequest;
use App\Http\Resources\Master\VehicleResource;
use App\Models\Master\VehicleModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(ListDataRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $query = VehicleModel::with('brand', 'type');

        // Apply search filters if search query is provided
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('brand', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });

                $query->whereHas('type', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $customers = $query->paginate($request->query('perPage') ?? 10, ['*'], 'page', $request->query('page') ?? 1);

        $list = $customers->isEmpty() ? null : VehicleResource::collection($customers);

        return response()->success([
            'list' => $list,
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        ], 200, 'All vehicle retrieved successfully');
    }
    public function showUserVehicle(ListDataRequest $request, $user_id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $query = VehicleModel::with('brand', 'type');

        // Apply search filters if search query is provided
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('brand', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });

                $query->whereHas('type', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $query->where('user_id', $user_id);

        $customers = $query->paginate($request->query('perPage') ?? 10, ['*'], 'page', $request->query('page') ?? 1);

        $list = $customers->isEmpty() ? null : VehicleResource::collection($customers);

        return response()->success([
            'list' => $list,
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        ], 200, 'All user vehicle retrieved successfully');
    }

    public function store(VehicleRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $userLogin = auth("api")->user();

        //if request has customer_id then set user_id to request customer id, if not set to request user_id
        $user_id = $request->customer_id ?? $request->user_id;

        $vehicle = VehicleModel::create($request->validated());
        // Set the default status to 0 after creating the record
        $vehicle->user_id = $user_id ?? $userLogin->user_id;
        $vehicle->save();

        return response()->success(new VehicleResource($vehicle), 201, 'Vehicle  created successfully');
    }

    public function show(VehicleModel $vehicle, $id)
    {

        try {
            // find with relationship brand and type
            $vehicle = VehicleModel::with('brand', 'type')->findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Vehicle  not found', 404);
        }

        return response()->success(new VehicleResource($vehicle), 200, 'Vehicle  retrieved successfully');
    }

    public function update(VehicleRequest $request, $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            $vehicle = VehicleModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Vehicle  not found', 404);
        }

        $vehicle->update($request->validated());
        return response()->success(new VehicleResource($vehicle), 200, 'Vehicle  updated successfully');
    }

    public function destroy($id)
    {
        try {
            $vehicle = VehicleModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Vehicle  not found', 404);
        }

        $vehicle->delete(); // Soft delete
        return response()->success(null, 200, 'Vehicle  deleted successfully');
    }
}
