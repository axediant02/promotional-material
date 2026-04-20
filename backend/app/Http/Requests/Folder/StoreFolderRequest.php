<?php

namespace App\Http\Requests\Folder;

use Illuminate\Foundation\Http\FormRequest;

class StoreFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folder_name' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'exists:users,user_id'],
        ];
    }
}
