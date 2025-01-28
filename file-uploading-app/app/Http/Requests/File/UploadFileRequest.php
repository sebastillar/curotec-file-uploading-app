<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
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
                'max:' . config('files.max_size'),
                'mimes:' . implode(',', config('files.allowed_types')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => trans('files.validation.file_size', [
                'size' => config('files.max_size') / 1024
            ]),
            'file.mimes' => trans('files.validation.file_types', [
                'types' => implode(', ', config('files.allowed_types'))
            ]),
        ];
    }
}
