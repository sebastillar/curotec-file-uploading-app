<?php

namespace App\Http\Requests\FileCollaborator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFileCollaboratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('file_collaborators')
                    ->where('file_id', $this->file->id)
                    ->whereNull('revoked_at')
            ],
            'role' => ['required', Rule::in(['viewer', 'commenter', 'editor'])]
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.unique' => 'This user is already a collaborator on this file.',
        ];
    }
}
