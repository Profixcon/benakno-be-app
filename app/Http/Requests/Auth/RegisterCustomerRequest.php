<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RegisterCustomerRequest extends FormRequest
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
            'email' => 'required|string|email|unique:access_auth,email',
            'password' => 'required|string|min:8',
            'phone' => 'required|string',
            'is_google' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name cannot be empty',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name can be a maximum of 255 characters',
            'photo.file' => 'Photo Profile must be a file',
            'email.required' => 'Email cannot be empty',
            'email.string' => 'Email must be a string',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email is already registered',
            'password.required' => 'Password cannot be empty',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password must be at least 8 characters',
            'phone.required' => 'Phone cannot be empty',
            'phone.string' => 'Phone must be a string',
            'is_google.required' => 'Is Google cannot be empty',
            'is_google.boolean' => 'Is Google must be a boolean',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}

