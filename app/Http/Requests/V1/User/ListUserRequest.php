<?php

namespace App\Http\Requests\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', Rule::in([0, 1, '0', '1', true, false, 'true', 'false'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sort_by' => ['nullable', Rule::in(['createdDate', 'modifiedDate', 'name', 'email'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('date_from') && $this->filled('date_to')) {
                if (strtotime((string) $this->input('date_to')) < strtotime((string) $this->input('date_from'))) {
                    $validator->errors()->add('date_to', 'Tanggal akhir harus sama atau setelah tanggal mulai.');
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $query = $this->query->all();

        return [
            'search' => $this->input('search'),
            // `active=0` harus tetap terbaca (bukan dianggap "kosong" seperti filled()).
            'active' => array_key_exists('active', $query) ? $this->input('active') : null,
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'sort_by' => $this->input('sort_by'),
            'sort_direction' => $this->input('sort_direction'),
        ];
    }
}
