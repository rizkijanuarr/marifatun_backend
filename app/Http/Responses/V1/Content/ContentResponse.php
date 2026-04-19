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
            'result' => $content->result,
            'revision_count' => (int) $content->revision_count,
            'revision_remaining' => max(Content::MAX_REVISIONS - (int) $content->revision_count, 0),
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
