<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UserCredit\CreateUserCreditRequest;
use App\Http\Requests\V1\UserCredit\UpdateUserCreditRequest;
use App\Http\Responses\base\BaseResponse;
use App\Http\Responses\V1\UserCredit\UserCreditListResponse;
use App\Http\Responses\V1\UserCredit\UserCreditResponse;
use App\Models\User;
use App\Services\V1\UserCreditService;
use Illuminate\Http\Request;

class UserCreditController extends Controller
{
    public function __construct(private readonly UserCreditService $service) {}

    /**
     * List User Credits
     *
     * Menampilkan daftar saldo kredit (paginated).
     *
     * - `ADMIN` dapat melihat kredit seluruh user (opsional difilter via
     *   query `user_id`).
     * - `MARIFATUN_USER` hanya dapat melihat kreditnya sendiri (filter
     *   `user_id` diabaikan / dipaksa ke user yang sedang login).
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`, `MARIFATUN_USER`
     *
     * @tags User Credit
     */
    public function index(Request $request): UserCreditListResponse
    {
        /** @var User $user */
        $user = $request->user();

        $filters = [];

        if ($user->hasRole(RoleEnum::ADMIN->value)) {
            if ($request->filled('user_id')) {
                $filters['user_id'] = $request->input('user_id');
            }
        } else {
            $filters['user_id'] = $user->id;
        }

        $paginator = $this->service->paginate(
            perPage: (int) $request->input('per_page', 15),
            filters: $filters,
        );

        return UserCreditListResponse::fromPaginator($paginator);
    }

    /**
     * Create User Credit
     *
     * Menambahkan record kredit untuk user tertentu.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags User Credit
     */
    public function store(CreateUserCreditRequest $request): UserCreditResponse
    {
        $credit = $this->service->create($request->validated());

        return UserCreditResponse::fromModel($credit->load('user'), 'User credit berhasil dibuat', 201);
    }

    /**
     * Show User Credit
     *
     * Detail kredit user berdasarkan UUID.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags User Credit
     */
    public function show(string $userCredit): UserCreditResponse
    {
        return UserCreditResponse::fromModel($this->service->find($userCredit)->load('user'));
    }

    /**
     * Update User Credit
     *
     * Update nominal kredit atau tanggal klaim harian user.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags User Credit
     */
    public function update(UpdateUserCreditRequest $request, string $userCredit): UserCreditResponse
    {
        $credit = $this->service->update($userCredit, $request->validated());

        return UserCreditResponse::fromModel($credit->load('user'), 'User credit berhasil diperbarui');
    }

    /**
     * Delete User Credit
     *
     * Soft delete record kredit user.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags User Credit
     */
    public function destroy(string $userCredit): BaseResponse
    {
        $this->service->delete($userCredit);

        return BaseResponse::make(null, 'User credit berhasil dihapus');
    }
}
