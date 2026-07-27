<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\UserStatus;

final readonly class UserData
{
    public function __construct(
        public string      $name,
        public string      $email,
        public ?string     $password,
        public UserStatus  $status = UserStatus::Active,
        public ?string     $phone = null,
        public ?string     $avatar = null,
        public ?int        $companyId = null,
        public ?int        $branchId = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name:      $data['name'],
            email:     $data['email'],
            password:  $data['password'] ?? null,
            status:    isset($data['status'])
                           ? UserStatus::from($data['status'])
                           : UserStatus::Active,
            phone:     $data['phone'] ?? null,
            avatar:    $data['avatar'] ?? null,
            companyId: isset($data['company_id']) ? (int) $data['company_id'] : null,
            branchId:  isset($data['branch_id'])  ? (int) $data['branch_id']  : null,
        );
    }

    public static function fromRequest(\Illuminate\Foundation\Http\FormRequest $request): self
    {
        return self::fromArray($request->validated());
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name'       => $this->name,
            'email'      => $this->email,
            'password'   => $this->password,
            'status'     => $this->status->value,
            'phone'      => $this->phone,
            'avatar'     => $this->avatar,
            'company_id' => $this->companyId,
            'branch_id'  => $this->branchId,
        ], fn ($v) => $v !== null);
    }
}
