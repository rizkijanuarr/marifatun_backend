<?php

namespace App\Http\Requests\V1\UserCredit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credits' => ['sometimes', 'integer', 'min:0'],
            'last_daily_claim' => ['nullable', 'date'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
