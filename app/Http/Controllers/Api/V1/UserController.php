<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\CreateUserRequest;
use App\Http\Requests\V1\User\UpdateUserRequest;
use App\Http\Responses\base\BaseResponse;
use App\Http\Responses\V1\User\UserListResponse;
use App\Http\Responses\V1\User\UserResponse;
use App\Services\V1\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    /**
     * List Users
     *
     * Menampilkan daftar users (paginated). Mendukung filter `search` (name/email)
     * dan `active`, serta parameter `per_page`.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags User
     */
    public function index(Request $request): UserListResponse
    {
        $paginator = $this->service->paginate(
            perPage: (int) $request->input('per_page', 15),
            filters: [
                'search' => $request->input('search'),
                'active' => $request->input('active'),
            ],
        );

        return UserListResponse::fromPaginator($paginator);
    }

    /**
     * Create User
     *
     * Membuat user baru. Admin bisa menentukan `role` (default: `MARIFATUN_USER`).
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags User
     */
    public function store(CreateUserRequest $request): UserResponse
    {
        $user = $this->service->create($request->validated());

        return UserResponse::fromModel($user, 'User berhasil dibuat', 201);
    }

    /**
     * Show User
     *
     * Detail user berdasarkan UUID.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags User
     */
    public function show(string $user): UserResponse
    {
        return UserResponse::fromModel($this->service->find($user));
    }

    /**
     * Update User
     *
     * Update data user (nama, email, password, role, status aktif).
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags User
     */
    public function update(UpdateUserRequest $request, string $user): UserResponse
    {
        $updated = $this->service->update($user, $request->validated());

        return UserResponse::fromModel($updated, 'User berhasil diperbarui');
    }

    /**
     * Delete User
     *
     * Soft delete user.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags User
     */
    public function destroy(string $user): BaseResponse
    {
        $this->service->delete($user);

        return BaseResponse::make(null, 'User berhasil dihapus');
    }
}
