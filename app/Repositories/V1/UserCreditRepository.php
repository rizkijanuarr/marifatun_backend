<?php

namespace App\Repositories\V1;

use App\Models\UserCredit;
use App\Repositories\base\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class UserCreditRepository extends BaseRepository
{
    public function __construct(UserCredit $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->with('user')->orderByDesc('createdDate');
    }

    public function findByUserId(string $userId): ?UserCredit
    {
        return $this->query()->where('user_id', $userId)->first();
    }
}
