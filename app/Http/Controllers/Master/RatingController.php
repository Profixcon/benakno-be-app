<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ListDataRequest;
use App\Http\Requests\Master\RatingRequest;
use App\Http\Resources\Master\RatingResource;
use App\Models\Auth\AuthModel;
use App\Models\Master\RatingModel;
use App\Models\Users\MitraModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(ListDataRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $query = RatingModel::with('customer', 'mitra', 'service');

        // Apply search filters if search query is provided
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });

                $query->whereHas('mitra', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $customers = $query->paginate($request->query('perPage') ?? 10, ['*'], 'page', $request->query('page') ?? 1);

        $list = $customers->isEmpty() ? null : RatingResource::collection($customers);

        return response()->success([
            'list' => $list,
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        ], 200, 'All rating retrieved successfully');
    }
    public function showCustomerRating(ListDataRequest $request, $user_id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $user = AuthModel::with('customer')->find($user_id);

        if (!$user) {
            return response()->error('Customer not found', 404);
        }

        $query = RatingModel::with('customer', 'mitra', 'service');

        // Apply search filters if search query is provided
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });

                $query->whereHas('mitra', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $query->where('user_id', $user_id);

        $customers = $query->paginate($request->query('perPage') ?? 10, ['*'], 'page', $request->query('page') ?? 1);

        $list = $customers->isEmpty() ? null : RatingResource::collection($customers);

        return response()->success([
            'list' => $list,
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        ], 200, 'All user rating retrieved successfully');
    }
    public function showMitraRating(ListDataRequest $request, $user_id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $user = AuthModel::with('mitra')->find($user_id);

        if (!$user) {
            return response()->error('Mitra not found', 404);
        }

        $query = RatingModel::with('customer', 'mitra', 'service');

        // Apply search filters if search query is provided
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });

                $query->whereHas('mitra', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        $query->where('mitra_id', $user_id);

        $customers = $query->paginate($request->query('perPage') ?? 10, ['*'], 'page', $request->query('page') ?? 1);

        $list = $customers->isEmpty() ? null : RatingResource::collection($customers);

        return response()->success([
            'list' => $list,
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        ], 200, 'All mitra rating retrieved successfully');
    }

    public function store(RatingRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $user = AuthModel::with('mitra')->find($request->mitra_id);

        if (!$user) {
            return response()->error('Mitra not found', 404);
        }

        //check if request service id already on rating
        $rating = RatingModel::where('service_id', $request->service_id)->first();
        if ($rating) {
            return response()->error('Service already rated', 409);
        }

        $userLogin = auth("api")->user();

        $rating = RatingModel::create($request->validated());
        // Set the default status to 0 after creating the record
        $rating->user_id = $userLogin->user_id;
        $rating->save();

        return response()->success(new RatingResource($rating), 201, 'Rating  created successfully');
    }

    public function show(RatingModel $rating, $id)
    {

        try {
            // find with relationship brand and type
            $rating = RatingModel::with('customer', 'mitra', 'service')->findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Rating  not found', 404);
        }

        return response()->success(new RatingResource($rating), 200, 'Rating  retrieved successfully');
    }

    public function update(RatingRequest $request, $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            $rating = RatingModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Rating  not found', 404);
        }

        $rating->update($request->validated());
        return response()->success(new RatingResource($rating), 200, 'Rating  updated successfully');
    }

    public function destroy($id)
    {
        try {
            $rating = RatingModel::findOrFail($id); // Find the record by ID or throw an exception
        } catch (ModelNotFoundException $e) {
            return response()->error('Rating  not found', 404);
        }

        $rating->delete(); // Soft delete
        return response()->success(null, 200, 'Rating  deleted successfully');
    }
}
