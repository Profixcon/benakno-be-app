<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public $validator = null;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nopol' => 'required|string|max:15|unique:t_vehicle,nopol',
            'brand_id' => 'required|exists:m_vehicle_brand,id',
            'type_id' => 'required|exists:m_vehicle_type,id',
            'year' => 'required|numeric',
            'type_vehicle' => 'required|in:motor,mobil',
        ];
    }

    public function messages()
    {
        return [
            'nopol.required' => 'Vehicle nopol cannot be empty',
            'nopol.string' => 'Vehicle nopol must be a string',
            'nopol.max' => 'Vehicle nopol can have a maximum of 15 characters',
            'nopol.unique' => 'Vehicle nopol must be unique',
            'brand_id.required' => 'Vehicle brand cannot be empty',
            'brand_id.exists' => 'Vehicle brand must be exists',
            'type_id.required' => 'Vehicle type cannot be empty',
            'type_id.exists' => 'Vehicle type must be exists',
            'type_vehicle.required' => 'Vehicle type cannot be empty',
            'type_vehicle.in' => 'Vehicle type must be motor or mobil',
            'year.required' => 'Vehicle year cannot be empty',
            'year.numeric' => 'Vehicle year must be a number',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
