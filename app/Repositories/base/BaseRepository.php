<?php

namespace App\Repositories\base;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->query(), $filters);

        return $query->paginate($perPage);
    }

    public function find(string $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findOrFail(string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->newInstance()->fill($data)->save()
            ? $this->model->refresh()
            : $this->model;
    }

    public function store(array $data): Model
    {
        $model = $this->model->newInstance();
        $model->fill($data);
        $model->save();

        return $model->refresh();
    }

    public function update(string $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->fill($data);
        $model->save();

        return $model->refresh();
    }

    public function delete(string $id): bool
    {
        $model = $this->findOrFail($id);

        return (bool) $model->delete();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $column => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $query->where($column, $value);
        }

        return $query;
    }
}
