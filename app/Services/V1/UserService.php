<?php

namespace App\Services\V1;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Repositories\V1\UserRepository;
use App\Services\base\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $role = $data['role'] ?? RoleEnum::MARIFATUN_USER->value;

            $user = $this->repository->store([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'active' => $data['active'] ?? true,
            ]);

            $user->assignRole($role);

            return $user->fresh(['roles']);
        });
    }

    public function update(string $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $payload = [];
            foreach (['name', 'email', 'active'] as $k) {
                if (array_key_exists($k, $data)) {
                    $payload[$k] = $data[$k];
                }
            }
            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user = $this->repository->update($id, $payload);

            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            return $user->fresh(['roles']);
        });
    }

    public function find(string $id): User
    {
        return $this->repository->query()->with('roles')->findOrFail($id);
    }
}
