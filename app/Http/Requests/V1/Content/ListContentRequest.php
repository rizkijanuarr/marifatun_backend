<?php

namespace App\Http\Requests\V1\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'content_type' => ['nullable', 'string', 'max:64'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            /** Hanya bila dikirim: true = aktif, false = tidak aktif; tanpa param = semua. */
            'active' => ['nullable', 'boolean'],
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
        $filters = [
            'search' => $this->input('search'),
            'content_type' => $this->input('content_type'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'sort_direction' => $this->input('sort_direction'),
            'include_user_search' => true,
        ];

        if ($this->has('active')) {
            $filters['active'] = $this->boolean('active');
        }

        return $filters;
    }
}
