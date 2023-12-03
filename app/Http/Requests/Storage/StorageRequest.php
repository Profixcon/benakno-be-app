<?php

namespace App\Http\Requests\Storage;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StorageRequest extends FormRequest
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
            'file' => 'required|mimes:pdf,xlsx,docx,png,jpg,jpeg|max:2048',
            'folder' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'The file field is required.',
            'file.mimes' => 'The file must be a file of type: pdf, doc, docx, jpeg, png, jpg, gif.',
            'file.max' => 'The file may not be greater than 2048 kilobytes.',
            'folder.required' => 'The folder field is required.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
