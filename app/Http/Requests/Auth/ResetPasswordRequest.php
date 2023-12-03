<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public $validator = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
            'conf_password' => 'required|string|min:8',
        ];
    }

    public function messages()
    {
        return [
            'old_password.required' => 'The old password field is required.',
            'old_password.string' => 'The old password must be a string.',
            'old_password.min' => 'The old password must be at least 8 characters.',
            'new_password.required' => 'The new password field is required.',
            'new_password.string' => 'The new password must be a string.',
            'new_password.min' => 'The new password must be at least 8 characters.',
            'conf_password.required' => 'The confirm password field is required.',
            'conf_password.string' => 'The confirm password must be a string.',
            'conf_password.min' => 'The confirm password must be at least 8 characters.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
