<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = \App\Models\User::findOrFail($this->route('user'));

        return $this->user()?->can('update', $target) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name'       => ['sometimes', 'required', 'string', 'max:255'],
            'email'      => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password'   => ['nullable', Password::defaults()],
            'phone'      => ['nullable', 'string', 'max:20'],
            'status'     => ['nullable', Rule::enum(UserStatus::class)],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'branch_id'  => ['nullable', 'integer', 'exists:branches,id'],
            'role'       => ['nullable', 'string', 'exists:roles,name'],
        ];
    }
}
