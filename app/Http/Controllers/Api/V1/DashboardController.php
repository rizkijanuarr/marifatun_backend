<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\V1\Dashboard\AdminDashboardResponse;
use App\Http\Responses\V1\Dashboard\UserDashboardResponse;
use App\Services\V1\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service) {}

    /**
     * Admin Dashboard Summary
     *
     * Ringkasan data global: total user, total konten, total topup pending, total
     * kredit beredar, daftar topup terbaru, dan konten terbaru.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`
     *
     * @tags Dashboard
     */
    public function admin(): AdminDashboardResponse
    {
        return AdminDashboardResponse::fromSummary($this->service->adminSummary());
    }

    /**
     * User Dashboard Summary
     *
     * Ringkasan untuk user yang sedang login: sisa kredit, status klaim harian,
     * history topup, dan konten terbaru.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `MARIFATUN_USER`
     *
     * @tags Dashboard
     */
    public function user(Request $request): UserDashboardResponse
    {
        return UserDashboardResponse::fromSummary(
            $this->service->userSummary($request->user()->id),
        );
    }
}
