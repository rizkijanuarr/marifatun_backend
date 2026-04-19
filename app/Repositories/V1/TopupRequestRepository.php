<?php

namespace App\Repositories\V1;

use App\Models\TopupRequest;
use App\Repositories\base\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class TopupRequestRepository extends BaseRepository
{
    public function __construct(TopupRequest $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['user', 'approver'])->orderByDesc('createdDate');
    }
}
