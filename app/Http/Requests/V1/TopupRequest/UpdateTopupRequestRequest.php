<?php

namespace App\Http\Requests\V1\TopupRequest;

use App\Enums\PaymentMethodEnum;
use App\Enums\TopupStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTopupRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'numeric', 'min:1'],
            'credits' => ['sometimes', 'integer', 'min:1'],
            'payment_method' => ['sometimes', Rule::in(PaymentMethodEnum::values())],
            'payment_proof' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', Rule::in(TopupStatusEnum::values())],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
