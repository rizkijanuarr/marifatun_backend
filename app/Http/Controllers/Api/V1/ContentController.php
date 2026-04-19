<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Content\CreateContentRequest;
use App\Http\Requests\V1\Content\UpdateContentRequest;
use App\Http\Responses\base\BaseResponse;
use App\Http\Responses\base\ErrorResponse;
use App\Http\Responses\V1\Content\ContentListResponse;
use App\Http\Responses\V1\Content\ContentResponse;
use App\Models\User;
use App\Services\V1\ContentService;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(private readonly ContentService $service) {}

    /**
     * List Contents
     *
     * Menampilkan daftar konten (paginated). `MARIFATUN_USER` hanya melihat kontennya
     * sendiri, sedangkan `ADMIN` dapat melihat seluruh konten (bisa difilter via `user_id`).
     * Mendukung filter `content_type` dan `search`.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`, `MARIFATUN_USER`
     *
     * @tags Content
     */
    public function index(Request $request): ContentListResponse
    {
        /** @var User $user */
        $user = $request->user();

        $filters = [
            'content_type' => $request->input('content_type'),
            'search' => $request->input('search'),
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

        return ContentListResponse::fromPaginator($paginator);
    }

    /**
     * Create Content
     *
     * Membuat konten baru. Sistem akan memanggil LLM (OpenRouter) untuk menghasilkan
     * `result` dan mengonsumsi 1 kredit dari saldo user. Jika saldo 0, request akan ditolak.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`, `MARIFATUN_USER`
     *
     * @tags Content
     */
    public function store(CreateContentRequest $request): ContentResponse
    {
        /** @var User $user */
        $user = $request->user();

        $content = $this->service->create([
            ...$request->validated(),
            'user_id' => $user->id,
        ]);

        return ContentResponse::fromModel($content->load('user'), 'Konten berhasil dibuat', 201);
    }

    /**
     * Show Content
     *
     * Detail konten berdasarkan UUID. `MARIFATUN_USER` hanya bisa melihat konten
     * miliknya sendiri (akses lain dibalas `403 Forbidden`).
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`, `MARIFATUN_USER` (hanya konten miliknya)
     *
     * @tags Content
     */
    public function show(Request $request, string $content): ContentResponse|ErrorResponse
    {
        /** @var User $user */
        $user = $request->user();
        $model = $this->service->find($content);

        if (! $user->hasRole(RoleEnum::ADMIN->value) && $model->user_id !== $user->id) {
            return ErrorResponse::make('Forbidden', 403, 'FORBIDDEN');
        }

        return ContentResponse::fromModel($model->load('user'));
    }

    /**
     * Update Content (Revisi)
     *
     * Merevisi konten yang sudah ada. Client mengirim body dengan field yang
     * sama seperti Create Content (`content_type`, `topic`, `keywords`,
     * `target_audience`, `tone`). Sistem akan memanggil LLM ulang untuk
     * menghasilkan `result` versi baru.
     *
     * **Aturan:**
     * - Revisi **tidak** mengonsumsi kredit.
     * - Dibatasi maksimum **3x revisi per konten**. Revisi ke-4 akan ditolak
     *   dengan `422` dan pesan "Batas revisi untuk konten ini telah tercapai".
     * - Counter `revision_count` di response akan bertambah setiap revisi sukses.
     *
     * `MARIFATUN_USER` hanya bisa merevisi konten miliknya sendiri.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`, `MARIFATUN_USER` (hanya konten miliknya)
     *
     * @tags Content
     */
    public function update(UpdateContentRequest $request, string $content): ContentResponse|ErrorResponse
    {
        /** @var User $user */
        $user = $request->user();
        $existing = $this->service->find($content);

        if (! $user->hasRole(RoleEnum::ADMIN->value) && $existing->user_id !== $user->id) {
            return ErrorResponse::make('Forbidden', 403, 'FORBIDDEN');
        }

        $updated = $this->service->update($content, $request->validated());

        return ContentResponse::fromModel($updated->load('user'), 'Konten berhasil diperbarui');
    }

    /**
     * Delete Content
     *
     * Soft delete konten. `MARIFATUN_USER` hanya bisa menghapus konten miliknya sendiri.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`, `MARIFATUN_USER` (hanya konten miliknya)
     *
     * @tags Content
     */
    public function destroy(Request $request, string $content): BaseResponse|ErrorResponse
    {
        /** @var User $user */
        $user = $request->user();
        $existing = $this->service->find($content);

        if (! $user->hasRole(RoleEnum::ADMIN->value) && $existing->user_id !== $user->id) {
            return ErrorResponse::make('Forbidden', 403, 'FORBIDDEN');
        }

        $this->service->delete($content);

        return BaseResponse::make(null, 'Konten berhasil dihapus');
    }
}
