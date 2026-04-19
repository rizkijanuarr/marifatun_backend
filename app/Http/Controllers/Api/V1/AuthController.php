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
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $service) {}

    /**
     * Register
     *
     * Registrasi pengguna baru. Secara default user baru mendapatkan role `MARIFATUN_USER`
     * dan saldo 1 kredit gratis.
     *
     * **Akses:** Public (tanpa token)
     *
     * **Role yang dihasilkan:** `MARIFATUN_USER`
     *
     * @tags Auth
     * @unauthenticated
     */
    public function register(RegisterRequest $request): RegisterResponse
    {
        $payload = $this->service->register($request->validated());

        return RegisterResponse::fromPayload($payload);
    }

    /**
     * Login
     *
     * Login pengguna menggunakan email & password. Mengembalikan Bearer token (Sanctum)
     * yang wajib dikirim pada header `Authorization: Bearer {token}` untuk endpoint
     * yang memerlukan otentikasi.
     *
     * **Akses:** Public (tanpa token)
     *
     * **Role yang bisa login:** `ADMIN`, `MARIFATUN_USER`
     *
     * @tags Auth
     * @unauthenticated
     */
    public function login(LoginRequest $request): LoginResponse
    {
        $payload = $this->service->login($request->validated());

        return LoginResponse::fromPayload($payload);
    }

    /**
     * Forgot Password
     *
     * Reset password user. Client mengirim `email` dan `new_password`. Jika email
     * terdaftar, password user akan di-update dengan `new_password` yang diberikan.
     * Jika email tidak terdaftar, akan dikembalikan error validasi.
     *
     * **Akses:** Public (tanpa token)
     *
     * **Role yang bisa pakai:** `ADMIN`, `MARIFATUN_USER`
     *
     * @tags Auth
     * @unauthenticated
     */
    public function forgotPassword(ForgotPasswordRequest $request): BaseResponse
    {
        $payload = $this->service->forgotPassword($request->validated());

        return BaseResponse::make(
            data: $payload,
            message: 'Password berhasil diperbarui.',
            status: 200,
        );
    }

    /**
     * Logout
     *
     * Menghapus Bearer token yang sedang dipakai user saat ini (Sanctum
     * `currentAccessToken`). Setelah logout, token tersebut tidak dapat dipakai
     * lagi untuk request berikutnya.
     *
     * Pada halaman dokumentasi, sistem juga akan otomatis menghapus token yang
     * tersimpan di `localStorage` sehingga endpoint lain tidak lagi terisi token.
     *
     * **Akses:** Memerlukan Bearer token.
     *
     * **Role:** `ADMIN`, `MARIFATUN_USER`
     *
     * @tags Auth
     */
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
