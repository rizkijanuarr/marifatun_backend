<?php

namespace App\Http\Responses\V1\Content;

use App\Http\Responses\base\ListResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContentListResponse extends ListResponse
{
    /**
     * @param  array<string, mixed>|null  $statistics
     */
    public static function fromPaginator(
        LengthAwarePaginator $paginator,
        string $message = 'Success',
        ?array $statistics = null,
    ): self {
        $items = collect($paginator->items())->map(fn ($c) => ContentResponse::transform($c))->all();

        $meta = [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];

        if ($statistics !== null) {
            $meta['statistics'] = $statistics;
        }

        return new self(
            items: $items,
            message: $message,
            status: 200,
            meta: $meta,
        );
    }
}
