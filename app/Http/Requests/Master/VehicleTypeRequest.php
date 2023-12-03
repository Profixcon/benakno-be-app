<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class VehicleTypeRequest extends FormRequest
{
    public $validator = null;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vehicle Type name cannot be empty',
            'name.string' => 'Vehicle Type name must be a string',
            'name.max' => 'Vehicle Type name can have a maximum of 255 characters',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
