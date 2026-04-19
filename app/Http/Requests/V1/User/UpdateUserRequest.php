<?php

namespace App\Http\Requests\V1\User;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['nullable', Rule::in(RoleEnum::values())],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
