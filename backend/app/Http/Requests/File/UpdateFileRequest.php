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
            'folder_id' => ['sometimes', 'exists:folders,folder_id'],
            'file_name' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'in:image,video,pdf'],
        ];
    }
}
