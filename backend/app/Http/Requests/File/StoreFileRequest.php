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
            'folder_id' => ['required', 'exists:folders,folder_id'],
            'file' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif,video/mp4,video/quicktime,application/pdf'],
        ];
    }
}
