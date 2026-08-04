<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\User;
use App\Models\Company;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Support\Facades\Hash;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $password = $data['password'] ?? null;
        $email = $data['email'] ?? null;
        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;

        if ($email && $password) {
            $user = User::create([
                'name'              => $name,
                'email'             => $email,
                'phone'             => $phone,
                'password'          => Hash::make($password),
                'status'            => UserStatus::Active,
                'email_verified_at' => now(),
                'company_id'        => Company::first()?->id,
                'created_by'        => auth()->id(),
                'updated_by'        => auth()->id(),
            ]);

            $user->assignRole(UserRole::Customer->value);

            $data['user_id'] = $user->id;
        }

        unset($data['password']);
        unset($data['password_confirmation']);

        return $data;
    }
}
