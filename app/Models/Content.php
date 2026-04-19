<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use HasAuditFields, HasUuid, SoftDeletes;

    protected $table = 'contents';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'createdDate';

    const UPDATED_AT = 'modifiedDate';

    const DELETED_AT = 'deletedDate';

    protected $fillable = [
        'user_id',
        'content_type',
        'topic',
        'keywords',
        'target_audience',
        'tone',
        'video_platform',
        'video_key_message',
        'video_cta',
        'result',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'createdDate' => 'datetime',
            'modifiedDate' => 'datetime',
            'deletedDate' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
