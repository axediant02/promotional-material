<?php

namespace App\Http\Requests\ClientRequest;

use App\Models\ClientRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'request_type' => ['required', Rule::in([
                ClientRequest::TYPE_NEW_ASSET,
                ClientRequest::TYPE_UPDATE_ASSET,
            ])],
            'due_date' => ['prohibited'],
            'folder_id' => ['prohibited'],
            'client_id' => ['prohibited'],
            'status' => ['prohibited'],
        ];
    }
}
