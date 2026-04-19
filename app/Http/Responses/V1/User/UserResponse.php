<?php

namespace App\Http\Responses\V1\User;

use App\Http\Responses\base\BaseResponse;
use App\Models\User;

class UserResponse extends BaseResponse
{
    public static function fromModel(User $user, string $message = 'Success', int $status = 200): self
    {
        return new self(
            data: self::transform($user),
            message: $message,
            status: $status,
        );
    }

    public static function transform(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'active' => (bool) $user->active,
            'roles' => $user->getRoleNames(),
            'createdDate' => optional($user->createdDate)->toIso8601String(),
            'modifiedDate' => optional($user->modifiedDate)->toIso8601String(),
        ];
    }
}
