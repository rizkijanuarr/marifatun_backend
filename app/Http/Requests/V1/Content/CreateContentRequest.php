<?php

namespace App\Http\Requests\V1\Content;

use App\Enums\ContentTypeEnum;
use App\Enums\ToneEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content_type' => ['required', Rule::in(ContentTypeEnum::values())],
            'topic' => ['required', 'string', 'max:255'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'target_audience' => ['nullable', 'string', 'max:255'],
            'tone' => ['required', Rule::in(ToneEnum::values())],
        ];
    }
}
