<?php

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
            'photo' => 'nullable|file',
            'phone' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name cannot be empty',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name can be a maximum of 255 characters',
            'photo.file' => 'Photo Profile must be a file',
            'phone.required' => 'Phone cannot be empty',
            'phone.string' => 'Phone must be a string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
