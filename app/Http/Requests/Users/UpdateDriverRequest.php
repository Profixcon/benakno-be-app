<?php

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends FormRequest
{
    public $validator = null;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->route('user_id'); // Assuming 'user' is the parameter name in your route

        return [
            'name' => 'required|string|max:255',
            'photo' => 'nullable|file',
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('access_auth', 'email')->ignore($userId, 'user_id'),
            ],
            'password' => 'required|string|min:8',
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
            'email.required' => 'Email cannot be empty',
            'email.string' => 'Email must be a string',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email is already registered',
            'password.required' => 'Password cannot be empty',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password must be at least 8 characters',
            'phone.required' => 'Phone cannot be empty',
            'phone.string' => 'Phone must be a string',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
