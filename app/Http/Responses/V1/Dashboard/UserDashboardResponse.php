<?php

namespace App\Http\Responses\V1\Dashboard;

use App\Http\Responses\base\BaseResponse;

class UserDashboardResponse extends BaseResponse
{
    public static function fromSummary(array $summary): self
    {
        return new self(
            data: $summary,
            message: 'User dashboard summary',
            status: 200,
        );
    }
}
