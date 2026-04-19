<?php

namespace App\Http\Responses\V1\Dashboard;

use App\Http\Responses\base\BaseResponse;

class AdminDashboardResponse extends BaseResponse
{
    public static function fromSummary(array $summary): self
    {
        return new self(
            data: $summary,
            message: 'Admin dashboard summary',
            status: 200,
        );
    }
}
