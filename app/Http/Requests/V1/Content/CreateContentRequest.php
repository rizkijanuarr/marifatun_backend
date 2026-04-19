<?php

namespace App\Http\Requests\V1\Content;

use App\Enums\ContentTypeEnum;
use App\Enums\ToneEnum;
use App\Rules\MaxWords;
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
        $isVideoScript = $this->input('content_type') === ContentTypeEnum::VIDEO_SCRIPT->value;

        return [
            'content_type' => ['required', Rule::in(ContentTypeEnum::values())],
            'topic' => ['required', 'string', new MaxWords(1000)],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'target_audience' => ['nullable', 'string', 'max:255'],
            'tone' => ['required', Rule::in(ToneEnum::values())],
            'video_platform' => array_merge(
                [Rule::requiredIf($isVideoScript), 'nullable', 'string', 'max:64'],
                $isVideoScript ? [Rule::in(['tiktok', 'instagram', 'linkedin'])] : []
            ),
            'video_key_message' => [Rule::requiredIf($isVideoScript), 'nullable', 'string', 'max:2000'],
            'video_cta' => [Rule::requiredIf($isVideoScript), 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'video_platform.required' => 'Platform video wajib dipilih.',
            'video_platform.in' => 'Platform video tidak dikenali.',
            'video_key_message.required' => 'Pesan utama wajib diisi.',
            'video_cta.required' => 'Call to action wajib diisi.',
        ];
    }
}
