<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopupRequest extends Model
{
    use HasUuid, HasAuditFields, SoftDeletes;

    protected $table = 'topup_requests';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'createdDate';
    const UPDATED_AT = 'modifiedDate';
    const DELETED_AT = 'deletedDate';

    protected $fillable = [
        'user_id',
        'amount',
        'credits',
        'payment_method',
        'payment_proof',
        'status',
        'approved_by',
        'approved_at',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'credits' => 'integer',
            'approved_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
