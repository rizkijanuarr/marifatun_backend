<?php

namespace App\Http\Requests\V1\TopupRequest;

use App\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTopupRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['nullable', 'numeric', 'min:1'],
            'credits' => ['nullable', 'integer', 'min:1'],
            'payment_method' => ['nullable', Rule::in(PaymentMethodEnum::values())],
            'payment_proof' => ['nullable', 'string', 'max:500'],
        ];
    }
}
