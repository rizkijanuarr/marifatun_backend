<?php

namespace App\Services\V1;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Repositories\V1\AuthRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private readonly AuthRepository $repository) {}

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = $this->repository->createUser([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'active' => true,
            ]);

            $user->assignRole(RoleEnum::MARIFATUN_USER->value);

            $token = $user->createToken('auth-token')->plainTextToken;

            return [
                'user' => $user->fresh(['roles']),
                'token' => $token,
                'token_type' => 'Bearer',
            ];
        });
    }

    public function login(array $data): array
    {
        $user = $this->repository->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak sesuai.'],
            ]);
        }

        if (! $user->active) {
            throw ValidationException::withMessages([
                'email' => ['Akun tidak aktif.'],
            ]);
        }

        $user->tokens()->where('name', 'auth-token')->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user->load('roles'),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();

            return;
        }

        $user->tokens()->delete();
    }

    public function forgotPassword(array $data): array
    {
        $user = $this->repository->findByEmail($data['email']);

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Email kamu belum terdaftar di database kami.'],
            ]);
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return [
            'email' => $user->email,
            'message' => 'Password berhasil diperbarui. Silakan login menggunakan password baru Anda.',
        ];
    }
}
