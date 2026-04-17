<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folder_id' => ['required', 'exists:folders,id'],
            'file' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif'],
        ];
    }
}
