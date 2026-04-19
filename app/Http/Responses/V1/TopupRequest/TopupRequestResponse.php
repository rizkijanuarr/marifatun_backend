<?php

namespace App\Http\Responses\V1\TopupRequest;

use App\Http\Responses\base\BaseResponse;
use App\Models\TopupRequest;

class TopupRequestResponse extends BaseResponse
{
    public static function fromModel(TopupRequest $topup, string $message = 'Success', int $status = 200): self
    {
        return new self(
            data: self::transform($topup),
            message: $message,
            status: $status,
        );
    }

    public static function transform(TopupRequest $topup): array
    {
        return [
            'id' => $topup->id,
            'user_id' => $topup->user_id,
            'amount' => (float) $topup->amount,
            'credits' => (int) $topup->credits,
            'payment_method' => $topup->payment_method,
            'payment_proof' => $topup->payment_proof,
            'status' => $topup->status,
            'approved_by' => $topup->approved_by,
            'approved_at' => optional($topup->approved_at)->toIso8601String(),
            'active' => (bool) $topup->active,
            'createdDate' => optional($topup->createdDate)->toIso8601String(),
            'modifiedDate' => optional($topup->modifiedDate)->toIso8601String(),
            'user' => $topup->relationLoaded('user') && $topup->user ? [
                'id' => $topup->user->id,
                'name' => $topup->user->name,
                'email' => $topup->user->email,
            ] : null,
            'approver' => $topup->relationLoaded('approver') && $topup->approver ? [
                'id' => $topup->approver->id,
                'name' => $topup->approver->name,
                'email' => $topup->approver->email,
            ] : null,
        ];
    }
}
