<?php

namespace App\Http\Requests\Chats;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    public $validator = null;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'room_id' => 'required|string',
            'sender_id' => 'required|string',
            'message' => 'required|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,doc,xlsx,xls,txt|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'room_id.required' => 'room_id is required!',
            'room_id.string' => 'room_id must be a string!',
            'sender_id.required' => 'sender_id is required!',
            'sender_id.string' => 'sender_id must be a string!',
            'message.required' => 'message is required!',
            'message.string' => 'message must be a string!',
            'file.file' => 'file must be a file!',
            'file.mimes' => 'file must be a jpg, jpeg, png, pdf, docx, doc, xlsx, xls, txt!',
            'file.max' => 'file size must be less than 2MB!',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }
}
