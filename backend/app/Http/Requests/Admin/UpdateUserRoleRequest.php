<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in([
                User::ROLE_ADMIN,
                User::ROLE_PRODUCTION,
                User::ROLE_AGENT,
                User::ROLE_CLIENT,
            ])],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $targetUser = $this->route('user');
                $actingUser = $this->user();

                if ($targetUser instanceof User && $actingUser && $targetUser->user_id === $actingUser->user_id) {
                    $validator->errors()->add('role', 'You cannot update your own role.');
                }
            },
        ];
    }
}
