<?php

namespace App\Repositories\V1;

use App\Models\User;
use App\Repositories\base\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (array_key_exists('active', $filters) && $filters['active'] !== null && $filters['active'] !== '') {
            $query->where('active', $this->normalizeActiveFilter($filters['active']));
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('createdDate', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('createdDate', '<=', $filters['date_to']);
        }

        $allowedSort = ['createdDate', 'modifiedDate', 'name', 'email'];
        $sortBy = $filters['sort_by'] ?? null;
        $sortBy = in_array($sortBy, $allowedSort, true) ? $sortBy : 'createdDate';
        $direction = strtolower((string) ($filters['sort_direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        return $query->with('roles')->orderBy($sortBy, $direction);
    }

    private function normalizeActiveFilter(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $v = is_string($value) ? strtolower(trim($value)) : $value;

        if ($v === '0' || $v === 0 || $v === 'false' || $v === 'off' || $v === 'no') {
            return false;
        }

        if ($v === '1' || $v === 1 || $v === 'true' || $v === 'on' || $v === 'yes') {
            return true;
        }

        return (bool) filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
