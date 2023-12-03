<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class VehicleBrandRequest extends FormRequest
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
            'photo' => 'required|string',
            'desc' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vehicle Brand name cannot be empty',
            'name.string' => 'Vehicle Brand name must be a string',
            'name.max' => 'Vehicle Brand name can have a maximum of 255 characters',
            'name.unique' => 'Vehicle Brand name must be unique',
            'photo.required' => 'Vehicle Brand photo cannot be empty',
            'photo.string' => 'Vehicle Brand photo must be a string',
            'desc.string' => 'Vehicle Brand description must be a string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
