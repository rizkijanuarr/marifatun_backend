<?php

namespace App\Http\Responses\V1\User;

use App\Http\Responses\base\ListResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserListResponse extends ListResponse
{
    public static function fromPaginator(LengthAwarePaginator $paginator, string $message = 'Success'): self
    {
        $items = collect($paginator->items())->map(fn ($u) => UserResponse::transform($u))->all();

        return new self(
            items: $items,
            message: $message,
            status: 200,
            meta: [
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
