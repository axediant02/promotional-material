<?php

namespace App\Http\Requests\Admin;

use App\Models\AssignedClient;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'production_id' => [
                'required',
                Rule::exists('users', 'user_id')->where('role', User::ROLE_PRODUCTION),
            ],
            'client_id' => [
                'required',
                Rule::exists('users', 'user_id')->where('role', User::ROLE_CLIENT),
            ],
            'status' => ['required', Rule::in([
                AssignedClient::STATUS_PENDING,
                AssignedClient::STATUS_IN_PROGRESS,
                AssignedClient::STATUS_DONE,
            ])],
        ];
    }
}
