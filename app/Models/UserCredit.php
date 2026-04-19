<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCredit extends Model
{
    use HasUuid, HasAuditFields, SoftDeletes;

    protected $table = 'user_credits';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'createdDate';
    const UPDATED_AT = 'modifiedDate';
    const DELETED_AT = 'deletedDate';

    protected $fillable = [
        'user_id',
        'credits',
        'last_daily_claim',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'last_daily_claim' => 'datetime',
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
