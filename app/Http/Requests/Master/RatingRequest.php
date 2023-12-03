<?php

namespace App\Http\Requests\Master;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RatingRequest extends FormRequest
{
    public $validator = null;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'service_id' => 'required|string|max:255',
            'mitra_id' => 'required|string|max:255',
            'rating' => 'required|numeric|max:5',
            'description' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'service_id.required' => 'Service ID is required!',
            'service_id.string' => 'Service ID must be string!',
            'service_id.max' => 'Service ID must be less than 255 characters!',
            'mitra_id.required' => 'Mitra ID is required!',
            'mitra_id.string' => 'Mitra ID must be string!',
            'mitra_id.max' => 'Mitra ID must be less than 255 characters!',
            'rating.required' => 'Rating is required!',
            'rating.numeric' => 'Rating must be numeric!',
            'rating.max' => 'Rating must be less than 5!',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
