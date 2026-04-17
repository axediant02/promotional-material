<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folder_id' => ['sometimes', 'exists:folders,id'],
            'original_name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
