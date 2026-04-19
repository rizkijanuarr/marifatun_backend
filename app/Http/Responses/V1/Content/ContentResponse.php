<?php

namespace App\Http\Responses\V1\Content;

use App\Http\Responses\base\BaseResponse;
use App\Models\Content;

class ContentResponse extends BaseResponse
{
    public static function fromModel(Content $content, string $message = 'Success', int $status = 200): self
    {
        return new self(
            data: self::transform($content),
            message: $message,
            status: $status,
        );
    }

    public static function transform(Content $content): array
    {
        return [
            'id' => $content->id,
            'user_id' => $content->user_id,
            'content_type' => $content->content_type,
            'topic' => $content->topic,
            'keywords' => $content->keywords,
            'target_audience' => $content->target_audience,
            'tone' => $content->tone,
            'video_platform' => $content->video_platform,
            'video_key_message' => $content->video_key_message,
            'video_cta' => $content->video_cta,
            'result' => $content->result,
            'active' => (bool) $content->active,
            'createdDate' => optional($content->createdDate)->toIso8601String(),
            'modifiedDate' => optional($content->modifiedDate)->toIso8601String(),
            'user' => $content->relationLoaded('user') && $content->user ? [
                'id' => $content->user->id,
                'name' => $content->user->name,
                'email' => $content->user->email,
            ] : null,
        ];
    }
}
