<?php

namespace App\Http\Responses\V1\User;

use App\Http\Responses\base\ListResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserListResponse extends ListResponse
{
    /**
     * @param  array<string, mixed>  $statistics
     */
    public static function fromPaginator(
        LengthAwarePaginator $paginator,
        string $message = 'Success',
        int $totalUsersInTable = 0,
        array $statistics = [],
    ): self {
        $items = collect($paginator->items())->map(fn ($u) => UserResponse::transform($u))->all();

        return new self(
            items: $items,
            message: $message,
            status: 200,
            meta: [
                'total_users' => $totalUsersInTable,
                'statistics' => $statistics,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        );
    }
}
