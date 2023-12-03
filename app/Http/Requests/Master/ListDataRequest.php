<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ListDataRequest extends FormRequest
{
    public $validator = null;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
            'search' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'page.integer' => 'Page must be an integer',
            'per_page.integer' => 'Per page must be an integer',
            'search.string' => 'Search must be a string',
            'latitude.numeric' => 'Latitude must be a number',
            'longitude.numeric' => 'Longitude must be a number',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
