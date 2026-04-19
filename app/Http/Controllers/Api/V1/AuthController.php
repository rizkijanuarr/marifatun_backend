<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Http\Responses\base\BaseResponse;
use App\Http\Responses\V1\Auth\LoginResponse;
use App\Http\Responses\V1\Auth\RegisterResponse;
use App\Models\User;
use App\Services\V1\AuthService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Auth', weight: 0)]
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $service) {}

    /** @unauthenticated */
    public function register(RegisterRequest $request): RegisterResponse
    {
        $payload = $this->service->register($request->validated());

        return RegisterResponse::fromPayload($payload);
    }

    /** @unauthenticated */
    public function login(LoginRequest $request): LoginResponse
    {
        $payload = $this->service->login($request->validated());

        return LoginResponse::fromPayload($payload);
    }

    /** @unauthenticated */
    public function forgotPassword(ForgotPasswordRequest $request): BaseResponse
    {
        $payload = $this->service->forgotPassword($request->validated());

        return BaseResponse::make(
            data: $payload,
            message: 'Password berhasil diperbarui.',
            status: 200,
        );
    }

    public function logout(Request $request): BaseResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->service->logout($user);

        return BaseResponse::make(
            data: null,
            message: 'Logout berhasil. Token telah dihapus.',
            status: 200,
        );
    }
}
