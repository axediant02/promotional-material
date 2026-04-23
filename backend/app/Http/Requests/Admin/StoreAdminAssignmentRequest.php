<?php

namespace App\Http\Requests\Admin;

use App\Models\AssignedClient;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreAdminAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
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
                Rule::exists('users', 'user_id')->where(function ($query): void {
                    $query
                        ->where('role', User::ROLE_CLIENT)
                        ->whereExists(function ($subQuery): void {
                            $subQuery
                                ->select(DB::raw(1))
                                ->from('client_requests')
                                ->whereColumn('client_requests.client_id', 'users.user_id');
                        });
                }),
            ],
            'status' => ['required', Rule::in([
                AssignedClient::STATUS_PENDING,
                AssignedClient::STATUS_IN_PROGRESS,
                AssignedClient::STATUS_DONE,
            ])],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.exists' => 'The selected client must have at least one request before assignment.',
        ];
    }
}
