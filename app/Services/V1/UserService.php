<?php

namespace App\Services\V1;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Repositories\V1\UserRepository;
use App\Services\base\BaseService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{paginator: LengthAwarePaginator, total_users: int, statistics: array<string, mixed>}
     */
    public function paginateList(int $perPage, array $filters): array
    {
        return [
            'paginator' => $this->repository->paginate($perPage, $filters),
            'total_users' => User::query()->count(),
            'statistics' => $this->userListStatistics(),
        ];
    }

    /**
     * Statistik global user (tidak terpengaruh filter list halaman).
     *
     * @return array<string, mixed>
     */
    public function userListStatistics(): array
    {
        $since30 = Carbon::now()->subDays(30)->startOfDay();

        $totalRole = [];
        foreach (RoleEnum::cases() as $role) {
            $totalRole[$role->value] = User::query()->role($role->value)->count();
        }

        return [
            'total_count_user_30_hari_terakhir' => User::query()
                ->where('createdDate', '>=', $since30)
                ->count(),
            'total_active_user' => User::query()->where('active', true)->count(),
            'total_not_active_user' => User::query()->where('active', false)->count(),
            'total_role' => $totalRole,
            'total_@gmail.com' => User::query()->where('email', 'like', '%@gmail.com')->count(),
            'total_@marifatun.test' => User::query()->where('email', 'like', '%@marifatun.test')->count(),
        ];
    }

    /**
     * Mengganti perilaku "hapus": toggle kolom `active` (0 ↔ 1). Baris user tidak di-soft-delete.
     */
    public function toggleActive(string $id): User
    {
        return DB::transaction(function () use ($id) {
            $user = $this->repository->findOrFail($id);
            $user->active = ! (bool) $user->active;
            $user->save();

            return $user->fresh(['roles']);
        });
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $role = $data['role'] ?? RoleEnum::MARIFATUN_USER->value;

            $user = $this->repository->store([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'active' => $data['active'] ?? true,
            ]);

            $user->assignRole($role);

            return $user->fresh(['roles']);
        });
    }

    public function update(string $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $payload = [];
            foreach (['name', 'email', 'active'] as $k) {
                if (array_key_exists($k, $data)) {
                    $payload[$k] = $data[$k];
                }
            }
            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user = $this->repository->update($id, $payload);

            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            return $user->fresh(['roles']);
        });
    }

    public function find(string $id): User
    {
        return $this->repository->query()->with('roles')->findOrFail($id);
    }
}
