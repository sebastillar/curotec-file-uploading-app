<?php

namespace App\Http\Requests\FileCollaborator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFileCollaboratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['viewer', 'commenter', 'editor'])]
        ];
    }
}
