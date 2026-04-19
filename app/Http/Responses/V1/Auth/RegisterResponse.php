<?php

namespace App\Http\Responses\V1\Auth;

use App\Http\Responses\base\BaseResponse;
use App\Models\User;

class RegisterResponse extends BaseResponse
{
    public static function fromPayload(array $payload, string $message = 'Registrasi berhasil'): self
    {
        /** @var User $user */
        $user = $payload['user'];

        return new self(
            data: [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'active' => $user->active,
                    'roles' => $user->getRoleNames(),
                ],
                'token' => $payload['token'],
                'token_type' => $payload['token_type'],
            ],
            message: $message,
            status: 201,
        );
    }
}
