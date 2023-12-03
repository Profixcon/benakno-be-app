<?php

namespace App\Http\Requests\Services;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public $validator = null;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => [
                'required',
                'string',
                Rule::exists('access_user', 'user_id'), // Check if 'user_id' exists in the 'access_user' table
            ],
            'service_id' => [
                'required',
                'string',
                Rule::exists('t_mitra_services', 'id'), // Check if 'service_id' exists in the 'service' table
            ],
            'vehicle_id' => 'required|string',
            'mitra_id' => [
                'required',
                'string',
                Rule::exists('access_mitra', 'user_id'), // Check if 'mitra_id' exists in the 'mitra' table
            ],
            'description' => 'required|string',
            'total' => 'required|integer',
            'service_fee' => 'required|integer',
            'payment_method' => 'required|string',
            'status' => 'nullable|integer',
            'sub_total' => 'required|integer',
            'customer_lat' => 'nullable|numeric',
            'customer_long' => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'user_id of customer is required!',
            'user_id.string' => 'user_id of customer must be a string!',
            'user_id.exists' => 'user_id of customer does not exist!',
            'service_id.required' => 'service_id of mitra services is required!',
            'service_id.string' => 'service_id of mitra services must be a string!',
            'service_id.exists' => 'service_id of mitra services does not exist!',
            'vehicle_id.required' => 'vehicle_id of customer is required!',
            'vehicle_id.string' => 'vehicle_id of customer must be a string!',
            'mitra_id.required' => 'mitra_id is required!',
            'mitra_id.string' => 'mitra_id must be a string!',
            'mitra_id.exists' => 'mitra_id does not exist!',
            'description.required' => 'description is required!',
            'description.string' => 'description must be a string!',
            'total.required' => 'total is required!',
            'total.integer' => 'total must be an integer!',
            'service_fee.required' => 'service_fee is required!',
            'service_fee.integer' => 'service_fee must be an integer!',
            'payment_method.required' => 'payment_method is required!',
            'payment_method.string' => 'payment_method must be a string!',
            'status.integer' => 'status must be an integer!',
            'sub_total.required' => 'sub_total is required!',
            'sub_total.integer' => 'sub_total must be an integer!',
            'customer_lat.numeric' => 'customer_lat must be a float!',
            'customer_long.numeric' => 'customer_long must be a float!',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
