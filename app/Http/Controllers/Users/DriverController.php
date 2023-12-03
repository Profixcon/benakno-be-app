<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ListDataRequest;
use App\Http\Requests\Users\UpdateDriverRequest;
use App\Http\Resources\Users\DriverResource;
use App\Models\Auth\AuthModel;
use App\Models\Users\DriverModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function index(ListDataRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $query = AuthModel::with('driver')->withRole(2);

        // Apply search filters if search query is provided
        $search = $request->search;
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('driver', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $drivers = $query->paginate($request->perPage ?? 10, ['*'], 'page', $request->page ?? 1);

        $list = $drivers->isEmpty() ? null : DriverResource::collection($drivers);

        return response()->success([
            'list' => $list,
            'pagination' => [
                'current_page' => $drivers->currentPage(),
                'per_page' => $drivers->perPage(),
                'total' => $drivers->total(),
                'last_page' => $drivers->lastPage(),
            ],
        ], 200, 'Success get all drivers');
    }

    public function show($id)
    {
        $driver = AuthModel::with('driver')->find($id);

        if (!$driver) {
            return response()->error('Driver not found', 404);
        }

        return response()->success(new DriverResource($driver), 200, 'Success get driver details');
    }

    public function update(UpdateDriverRequest $request, $user_id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $customer = DriverModel::find($user_id);

            if (!$customer) {
                return response()->error('Driver not found', 404);
            }

            // Update the customer's information
            $customer->name = $request->name;

            // Upload and update the photo if provided
            if (!is_null($request->photo)) {
                $photo = UploadFile($request, 'photos', 'photo', ['jpg', 'jpeg', 'png']);

                if (!$photo['status']) {
                    return response()->error($photo['message'], 400);
                }

                $customer->photo = $photo['data']['path'];
            }

            // Update other fields as needed
            $customer->phone = $request->phone;

            $customer->save();

            $auth = AuthModel::find($user_id);

            $auth->email = $request->email;
            $auth->password = Hash::make($request->password);

            $auth->save();

            $dataUser = AuthModel::where('user_id', $user_id)
                ->with([
                    'driver'
                ])
                ->first();

            $userResource = new DriverResource($dataUser);

            DB::commit(); // Commit the transaction if all operations are successful

            return response()->success($userResource, 200, 'Driver updated successfully');
        } catch (\Exception $e) {
            // return not valid token
            DB::rollback(); // Roll back the transaction if an error occurs
            return response()->error($e->getMessage(), 422);
        }
    }
}
