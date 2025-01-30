<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class AddVersionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:jpeg,png,pdf,doc,docx,xls,xlsx',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'The file size must not exceed 10MB.',
            'file.mimes' => 'The file must be of type: jpeg, png, pdf, doc, docx, xls, xlsx.',
        ];
    }
}
