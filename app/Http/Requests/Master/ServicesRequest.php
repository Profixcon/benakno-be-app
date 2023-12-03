<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ServicesRequest extends FormRequest
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
            'photo' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Service name cannot be empty',
            'name.string' => 'Service name must be a string',
            'name.max' => 'Service name can have a maximum of 255 characters',
            'photo.string' => 'Photo must be a string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
