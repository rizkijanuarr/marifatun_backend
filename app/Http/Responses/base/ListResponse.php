<?php

namespace App\Http\Responses\base;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ListResponse implements Responsable
{
    public function __construct(
        public iterable $items = [],
        public string $message = 'Success',
        public int $status = 200,
        public ?array $meta = null,
    ) {}

    public static function fromPaginator(LengthAwarePaginator $paginator, string $message = 'Success'): self
    {
        return new self(
            items: $paginator->items(),
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

    public static function make(iterable $items, string $message = 'Success'): self
    {
        return new self(items: $items, message: $message);
    }

    public function toResponse($request): JsonResponse
    {
        $items = $this->items;
        if ($items instanceof Collection) {
            $items = $items->all();
        }

        return response()->json([
            'status' => $this->status,
            'success' => $this->status >= 200 && $this->status < 300,
            'message' => $this->message,
            'data' => $items,
            'meta' => $this->meta ?? [
                'total' => is_countable($items) ? count($items) : null,
            ],
        ], $this->status);
    }
}
