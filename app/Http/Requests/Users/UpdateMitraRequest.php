<?php

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMitraRequest extends FormRequest
{
    public $validator = null;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'owner' => 'required|string',
            'name' => 'required|string|max:255',
            'photo' => 'nullable|file', // Accept file uploads for photo
            'phone' => 'required|string',
            'address' => 'required|string',
            'employees' => 'required|int',
            'business_permit' => 'nullable|file', // Accept file uploads for business_permit
            'is_motor' => 'required|boolean',
            'is_mobil' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'owner.required' => 'Owner cannot be empty',
            'owner.string' => 'Owner must be a string',
            'nama.required' => 'Name cannot be empty',
            'nama.string' => 'Name must be a string',
            'nama.max' => 'Name can be a maximum of 255 characters',
            'photo.file' => 'Photo Profile must be a file',
            'phone.required' => 'Phone cannot be empty',
            'phone.string' => 'Phone must be a string',
            'address.required' => 'Address cannot be empty',
            'address.string' => 'Address must be a string',
            'employees.required' => 'Employees cannot be empty',
            'employees.int' => 'Employees must be an integer',
            'business_permit.file' => 'Business Permit must be a file',
            'is_motor.required' => 'Is Motor cannot be empty',
            'is_motor.boolean' => 'Is Motor must be a boolean',
            'is_mobil.required' => 'Is Mobil cannot be empty',
            'is_mobil.boolean' => 'Is Mobil must be a boolean',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
