<?php

namespace App\Http\Requests\Folder;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folder_name' => ['sometimes', 'string', 'max:255'],
            'client_id' => ['sometimes', 'exists:users,user_id'],
        ];
    }
}
