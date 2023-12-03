<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Http\Requests\Location\CustomerRequest;
use App\Http\Resources\Location\CustomerResource;
use App\Models\Location\CustomerModel;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

    //get customer latitude and longitude by user id
    public function getCustomer($id)
    {
        //check if customer exist
        $customer = CustomerModel::where('user_id', $id)->first();
        if ($customer) {
            return response()->success(new CustomerResource($customer), 200, 'Succcessfully get customer location');
        } else {
            return response()->error('Customer not found', 404);
        }
    }

    //update customer location
    public function updateCustomer(CustomerRequest $request, $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        //check if customer exist
        $customer = CustomerModel::where('user_id', $id)->first();
        if ($customer) {
            $customer->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            $customer = CustomerModel::where('user_id', $id)->first();

            return response()->success(new CustomerResource($customer), 200, 'Succcessfully update customer location');
        } else {
            return response()->error('Customer not found', 404);
        }
    }
}
