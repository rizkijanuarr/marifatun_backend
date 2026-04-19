<?php

namespace App\Repositories\V1;

use App\Models\User;
use App\Repositories\base\BaseRepository;

class AuthRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    public function createUser(array $data): User
    {
        return $this->store($data);
    }
}
