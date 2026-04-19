<?php

namespace App\Http\Requests\V1\UserCredit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'uuid', Rule::exists('users', 'id')],
            'credits' => ['required', 'integer', 'min:0'],
            'last_daily_claim' => ['nullable', 'date'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
