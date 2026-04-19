<?php

namespace App\Http\Responses\base;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class ErrorResponse implements Responsable
{
    public function __construct(
        public string $message = 'Error',
        public int $status = 400,
        public ?string $errorCode = null,
        public mixed $errors = null,
    ) {}

    public static function make(string $message, int $status = 400, ?string $errorCode = null, mixed $errors = null): self
    {
        return new self($message, $status, $errorCode, $errors);
    }

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'status' => $this->status,
            'success' => false,
            'message' => $this->message,
            'error_code' => $this->errorCode,
            'errors' => $this->errors,
        ], $this->status);
    }
}
