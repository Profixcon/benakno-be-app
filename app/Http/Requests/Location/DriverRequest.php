<?php

namespace App\Http\Requests\Location;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DriverRequest extends FormRequest
{
    public $validator = null;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'latitude.required' => 'Driver latitude cannot be empty',
            'latitude.numeric' => 'Driver latitude must be a float',
            'longitude.required' => 'Driver longitude cannot be empty',
            'longitude.numeric' => 'Driver longitude must be a float',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
