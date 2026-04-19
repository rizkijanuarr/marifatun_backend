<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasAuditFields
{
    protected static function bootHasAuditFields(): void
    {
        static::creating(function (Model $model) {
            $userId = Auth::id();
            if (! empty($userId) && empty($model->createdBy ?? null)) {
                $model->createdBy = (string) $userId;
            }
        });

        static::updating(function (Model $model) {
            $userId = Auth::id();
            if (! empty($userId)) {
                $model->modifiedBy = (string) $userId;
            }
        });

        static::deleting(function (Model $model) {
            $userId = Auth::id();
            if (! empty($userId) && method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                $model->deletedBy = (string) $userId;
                $model->saveQuietly();
            }
        });
    }
}
