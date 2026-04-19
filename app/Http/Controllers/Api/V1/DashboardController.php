<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\V1\Dashboard\AdminDashboardResponse;
use App\Http\Responses\V1\Dashboard\UserDashboardResponse;
use App\Services\V1\DashboardService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service) {}

    #[Group('ROLE ADMIN', weight: 1)]
    public function admin(): AdminDashboardResponse
    {
        return AdminDashboardResponse::fromSummary($this->service->adminSummary());
    }

    #[Group('ROLE MARIFATUN_USER', weight: 2)]
    public function user(Request $request): UserDashboardResponse
    {
        return UserDashboardResponse::fromSummary(
            $this->service->userSummary($request->user()->id),
        );
    }
}
