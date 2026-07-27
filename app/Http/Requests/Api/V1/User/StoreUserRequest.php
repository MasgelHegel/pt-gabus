<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\User;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\User::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', Password::defaults()],
            'phone'      => ['nullable', 'string', 'max:20'],
            'status'     => ['nullable', Rule::enum(UserStatus::class)],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'branch_id'  => ['nullable', 'integer', 'exists:branches,id'],
            'role'       => ['nullable', 'string', 'exists:roles,name'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required'    => 'Nama wajib diisi.',
            'email.required'   => 'Email wajib diisi.',
            'email.unique'     => 'Email sudah digunakan.',
            'password.required'=> 'Password wajib diisi.',
        ];
    }
}
