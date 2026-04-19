<?php

namespace App\Http\Responses\V1\UserCredit;

use App\Http\Responses\base\BaseResponse;
use App\Models\UserCredit;

class UserCreditResponse extends BaseResponse
{
    public static function fromModel(UserCredit $credit, string $message = 'Success', int $status = 200): self
    {
        return new self(
            data: self::transform($credit),
            message: $message,
            status: $status,
        );
    }

    public static function transform(UserCredit $credit): array
    {
        return [
            'id' => $credit->id,
            'user_id' => $credit->user_id,
            'credits' => (int) $credit->credits,
            'last_daily_claim' => optional($credit->last_daily_claim)->toIso8601String(),
            'active' => (bool) $credit->active,
            'createdDate' => optional($credit->createdDate)->toIso8601String(),
            'modifiedDate' => optional($credit->modifiedDate)->toIso8601String(),
            'user' => $credit->relationLoaded('user') && $credit->user ? [
                'id' => $credit->user->id,
                'name' => $credit->user->name,
                'email' => $credit->user->email,
            ] : null,
        ];
    }
}
