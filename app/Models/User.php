<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasAuditFields, HasRoles, HasUuid, Notifiable, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'createdDate';

    const UPDATED_AT = 'modifiedDate';

    const DELETED_AT = 'deletedDate';

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'createdDate' => 'datetime',
            'modifiedDate' => 'datetime',
            'deletedDate' => 'datetime',
        ];
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'user_id');
    }
}
