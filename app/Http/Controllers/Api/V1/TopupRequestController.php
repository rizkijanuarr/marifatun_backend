<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\TopupRequest\CreateTopupRequestRequest;
use App\Http\Requests\V1\TopupRequest\UpdateTopupRequestRequest;
use App\Http\Responses\base\BaseResponse;
use App\Http\Responses\base\ErrorResponse;
use App\Http\Responses\V1\TopupRequest\TopupRequestListResponse;
use App\Http\Responses\V1\TopupRequest\TopupRequestResponse;
use App\Models\User;
use App\Services\V1\TopupRequestService;
use Illuminate\Http\Request;

class TopupRequestController extends Controller
{
    public function __construct(private readonly TopupRequestService $service) {}

    /**
     * List Topup Requests
     *
     * Menampilkan daftar permintaan topup (paginated). `MARIFATUN_USER` hanya melihat
     * request miliknya sendiri, `ADMIN` dapat melihat semua (bisa difilter via `user_id`).
     * Mendukung filter `status` (`pending`, `approved`, `rejected`).
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`, `MARIFATUN_USER`
     *
     * @tags Topup Request
     */
    public function index(Request $request): TopupRequestListResponse
    {
        /** @var User $user */
        $user = $request->user();

        $filters = [
            'status' => $request->input('status'),
        ];

        if (! $user->hasRole(RoleEnum::ADMIN->value)) {
            $filters['user_id'] = $user->id;
        } elseif ($request->filled('user_id')) {
            $filters['user_id'] = $request->input('user_id');
        }

        $paginator = $this->service->paginate(
            perPage: (int) $request->input('per_page', 15),
            filters: $filters,
        );

        return TopupRequestListResponse::fromPaginator($paginator);
    }

    /**
     * Create Topup Request
     *
     * Membuat permintaan topup kredit. Status awal `pending` menunggu approval admin.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`, `MARIFATUN_USER`
     *
     * @tags Topup Request
     */
    public function store(CreateTopupRequestRequest $request): TopupRequestResponse
    {
        /** @var User $user */
        $user = $request->user();

        $topup = $this->service->create([
            ...$request->validated(),
            'user_id' => $user->id,
        ]);

        return TopupRequestResponse::fromModel($topup->load(['user', 'approver']), 'Topup request berhasil dibuat', 201);
    }

    /**
     * Show Topup Request
     *
     * Detail permintaan topup. `MARIFATUN_USER` hanya bisa melihat request miliknya.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`, `MARIFATUN_USER` (hanya request miliknya)
     *
     * @tags Topup Request
     */
    public function show(Request $request, string $topupRequest): TopupRequestResponse|ErrorResponse
    {
        /** @var User $user */
        $user = $request->user();
        $model = $this->service->find($topupRequest);

        if (! $user->hasRole(RoleEnum::ADMIN->value) && $model->user_id !== $user->id) {
            return ErrorResponse::make('Forbidden', 403, 'FORBIDDEN');
        }

        return TopupRequestResponse::fromModel($model->load(['user', 'approver']));
    }

    /**
     * Update / Approve Topup Request
     *
     * Update status topup request. Jika status diubah menjadi `approved`, sistem akan
     * otomatis menambahkan `credits` ke saldo user dan mencatat `approved_by` +
     * `approved_at`.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags Topup Request
     */
    public function update(UpdateTopupRequestRequest $request, string $topupRequest): TopupRequestResponse
    {
        $updated = $this->service->update($topupRequest, $request->validated());

        return TopupRequestResponse::fromModel(
            $updated->load(['user', 'approver']),
            'Topup request berhasil diperbarui',
        );
    }

    /**
     * Delete Topup Request
     *
     * Soft delete topup request.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags Topup Request
     */
    public function destroy(string $topupRequest): BaseResponse
    {
        $this->service->delete($topupRequest);

        return BaseResponse::make(null, 'Topup request berhasil dihapus');
    }
}
