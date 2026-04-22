<?php

namespace App\Http\Requests\Production;

use App\Models\ClientRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductionRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                ClientRequest::STATUS_PENDING,
                ClientRequest::STATUS_IN_PROGRESS,
                ClientRequest::STATUS_DONE,
            ])],
            'due_date' => ['prohibited'],
        ];
    }
}
