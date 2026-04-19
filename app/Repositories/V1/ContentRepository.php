<?php

namespace App\Repositories\V1;

use App\Models\Content;
use App\Repositories\base\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class ContentRepository extends BaseRepository
{
    public function __construct(Content $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['content_type'])) {
            $query->where('content_type', $filters['content_type']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('topic', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%");
            });
        }

        return $query->with('user')->orderByDesc('createdDate');
    }
}
