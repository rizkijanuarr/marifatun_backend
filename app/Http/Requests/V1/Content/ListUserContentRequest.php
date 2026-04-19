<?php

namespace App\Http\Requests\V1\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Query untuk `GET /api/v1/user/contents` — hanya konten milik user yang login.
 * Daftar selalu difilter `active = true` di controller (parameter `active` tidak dipakai).
 *
 * Pencarian: topik, kata kunci, dan nada (`tone`). Rentang tanggal: `createdDate`.
 * Urut: `modifiedDate` asc/desc.
 */
class ListUserContentRequest extends FormRequest
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
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
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
        return [
            'search' => $this->input('search'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'sort_direction' => $this->input('sort_direction'),
            'include_user_search' => false,
            'include_tone_in_search' => true,
        ];
    }
}
