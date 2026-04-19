<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\CreateUserRequest;
use App\Http\Requests\V1\User\ListUserRequest;
use App\Http\Requests\V1\User\UpdateUserRequest;
use App\Http\Responses\V1\User\UserListResponse;
use App\Http\Responses\V1\User\UserResponse;
use App\Services\V1\UserService;
use Dedoc\Scramble\Attributes\Group;

#[Group('ROLE ADMIN', weight: 1)]
class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    public function index(ListUserRequest $request): UserListResponse
    {
        $perPage = (int) $request->input('per_page', 5);
        $perPage = max(1, min(100, $perPage));

        $result = $this->service->paginateList($perPage, $request->filters());

        return UserListResponse::fromPaginator(
            $result['paginator'],
            totalUsersInTable: $result['total_users'],
            statistics: $result['statistics'],
        );
    }

    public function store(CreateUserRequest $request): UserResponse
    {
        $user = $this->service->create($request->validated());

        return UserResponse::fromModel($user, 'User berhasil dibuat', 201);
    }

    public function show(string $user): UserResponse
    {
        return UserResponse::fromModel($this->service->find($user));
    }

    public function update(UpdateUserRequest $request, string $user): UserResponse
    {
        $updated = $this->service->update($user, $request->validated());

        return UserResponse::fromModel($updated, 'User berhasil diperbarui');
    }

    public function destroy(string $user): UserResponse
    {
        $updated = $this->service->toggleActive($user);

        return UserResponse::fromModel(
            $updated,
            'Status aktif user berhasil diubah (bukan penghapusan dari database).',
        );
    }
}
