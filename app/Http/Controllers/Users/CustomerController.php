<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ListDataRequest;
use App\Http\Requests\Users\UpdateCustomerRequest;
use App\Http\Resources\Users\CustomerResource;
use App\Models\Auth\AuthModel;
use App\Models\Users\CustomerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(ListDataRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $query = AuthModel::with('customer', 'ratingUser')->withRole(3);

        // Apply search filters if search query is provided
        $search = $request->query('search');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Apply sorting if sort_by and order query parameters are provided
        $sortBy = $request->input('sort_by');
        $order = $request->input('order');
        if ($sortBy && $order) {
            // Determine if the sorting column belongs to 'AuthModel' or 'customer' relationship
            $isSortInCustomer = str_starts_with($sortBy, 'customer.');
            $sortColumn = $isSortInCustomer ? str_replace('customer.', '', $sortBy) : $sortBy;

            // If sorting by a column in the 'customer' relationship, join the 'customer' table
            if ($isSortInCustomer) {
                $query->join('access_user', 'access_auth.user_id', '=', 'access_user.user_id');
            }

            // Apply dynamic sorting
            $query->orderBy($isSortInCustomer ? 'access_user.' . $sortColumn : 'access_auth.' . $sortColumn, $order);
        }

        $customers = $query->paginate($request->query('perPage') ?? 10, ['*'], 'page', $request->query('page') ?? 1);

        //check if ratingUser is empty array then set null
        foreach ($customers as $key => $value) {
            if ($value->ratingUser == "[]") {
                $customers[$key]->ratingUser = null;
            }
        }

        $list = $customers->isEmpty() ? null : CustomerResource::collection($customers);

        return response()->success([
            'list' => $list,
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        ], 200, 'Success get all customers');
    }

    public function show($id)
    {
        $customer = AuthModel::with('customer', 'ratingUser')->find($id);

        if (!$customer) {
            return response()->error('Customer not found', 404);
        }

        //check if ratingUser is empty array then set null
        if ($customer->ratingUser == "[]") {
            $customer->ratingUser = null;
        }

        return response()->success(new CustomerResource($customer), 200, 'Success get customer details');
    }

    public function update(UpdateCustomerRequest $request, $user_id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            // Check if the customer exists
            $customer = CustomerModel::find($user_id);

            if (!$customer) {
                return response()->error('Customer not found', 404);
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

            $dataUser = AuthModel::where('user_id', $user_id)
                ->with([
                    'customer'
                ])
                ->first();

            $userResource = new CustomerResource($dataUser);

            DB::commit(); // Commit the transaction if all operations are successful

            return response()->success($userResource, 200, 'Customer updated successfully');
        } catch (\Exception $e) {
            // return not valid token
            DB::rollback(); // Roll back the transaction if an error occurs
            return response()->error($e->getMessage(), 422);
        }
    }

}
