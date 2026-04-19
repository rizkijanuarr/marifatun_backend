<?php

namespace App\Http\Responses\base;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class BaseResponse implements Responsable
{
    public function __construct(
        public mixed $data = null,
        public string $message = 'Success',
        public int $status = 200,
    ) {}

    public static function make(mixed $data = null, string $message = 'Success', int $status = 200): self
    {
        return new self($data, $message, $status);
    }

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'status' => $this->status,
            'success' => $this->status >= 200 && $this->status < 300,
            'message' => $this->message,
            'data' => $this->data,
        ], $this->status);
    }
}
