# Marifatun (Knowledge) — Backend Laravel API

**Marifatun** merupakan sebuah sistem yang menggabungkan dengan LLM untuk tujuan copywriting Post LinkedIn, X, Thread, Facebook, dan Email Marketing.

---

## ✅ Urutan Pengerjaan (Checklist)

> Ikuti urutan ini secara runtun agar pengerjaan terstruktur dan tidak ada yang terlewat. (LAKUKANLAH SECARA BERTAHAP DAN RUNTUN)

### STEP 1 — Setup Struktur Folder

- [ ] Buat folder `app/Enums/`
- [ ] Buat folder `app/Http/Controllers/Api/V1/`
- [ ] Buat folder `app/Http/Requests/base/` dan `app/Http/Requests/V1/`
- [ ] Buat folder `app/Http/Responses/base/` dan `app/Http/Responses/V1/`
- [ ] Buat folder `app/Models/`
- [ ] Buat folder `app/Services/base/` dan `app/Services/V1/`
- [ ] Buat folder `app/Repositories/base/` dan `app/Repositories/V1/`
- [ ] Buat folder `app/Traits/`
- [ ] Pastikan semua folder sudah terbentuk sebelum lanjut ke step berikutnya

---

### STEP 2 — Database & Migrations

- [ ] Buat migration tabel `users` (UUID, soft delete, semua kolom standar)
- [ ] Buat migration tabel `user_credits` (UUID, soft delete, semua kolom standar)
- [ ] Buat migration tabel `contents` (UUID, soft delete, semua kolom standar)
- [ ] Buat migration tabel `topup_requests` (UUID, soft delete, semua kolom standar)
- [ ] Pastikan semua kolom Enum menggunakan tipe `string` bukan `ENUM` native MySQL
- [ ] Pastikan semua primary key menggunakan UUID (`string`)
- [ ] Jalankan `php artisan migrate` dan pastikan tidak ada error

---

### STEP 3 — Setup Spatie Role & Permission

- [ ] Install package `spatie/laravel-permission`
- [ ] Publish config dan migration Spatie
- [ ] Jalankan migration Spatie
- [ ] Buat file `app/Enums/RoleEnum.php` berisi konstanta role (`ADMIN`, `MARIFATUN_USER`)
- [ ] Tambahkan trait `HasRoles` pada model `User`
- [ ] Pastikan role disimpan sebagai `string` di database (gunakan `RoleEnum`)

---

### STEP 4 — Setup Seeder

- [ ] Buat `RoleSeeder` — seed semua role dari `RoleEnum`
- [ ] Buat `AdminSeeder` — seed akun admin default dengan role `ADMIN`
- [ ] Buat `UserSeeder` — seed contoh user dengan role `MARIFATUN_USER`
- [ ] Buat `DatabaseSeeder` — panggil semua seeder secara berurutan
- [ ] Jalankan `php artisan db:seed` dan pastikan tidak ada error

---

### STEP 5 — Setup Scramble (Docs API)

- [ ] Install package `dedoc/scramble`
- [ ] Publish config Scramble
- [ ] Pastikan route docs berjalan di `/docs/api`
- [ ] Konfigurasi info API (title, version, description) di config Scramble
- [ ] Verifikasi dokumentasi ter-generate dengan benar sebelum lanjut

---

### STEP 6 — Setup Base Class (Reusable Foundation)

> Wajib diselesaikan sebelum mengerjakan fitur apapun.

- [ ] Buat `app/Http/Responses/base/BaseResponse.php` — untuk single data
- [ ] Buat `app/Http/Responses/base/ListResponse.php` — untuk banyak data & pagination
- [ ] Buat `app/Http/Responses/base/ErrorResponse.php` — untuk error response
- [ ] Buat `app/Repositories/base/BaseRepository.php` — contract/interface dasar
- [ ] Buat `app/Services/base/BaseService.php` — contract/interface dasar
- [ ] Buat `app/Traits/` yang diperlukan (misal: `HasUuid`, `HasAuditFields`)
- [ ] Pastikan semua base class berfungsi sebelum lanjut ke fitur

---

### STEP 7 — Fitur Auth

- [ ] Buat `app/Enums/` yang diperlukan untuk Auth
- [ ] Buat Model `User` (UUID, soft delete, trait)
- [ ] Buat `app/Repositories/V1/AuthRepository.php`
- [ ] Buat `app/Services/V1/AuthService.php` (semua logika bisnis di sini)
- [ ] Buat `app/Http/Requests/V1/Auth/` — LoginRequest, RegisterRequest, ForgotPasswordRequest
- [ ] Buat `app/Http/Responses/V1/Auth/` — LoginResponse, RegisterResponse
- [ ] Buat `app/Http/Controllers/Api/V1/AuthController.php` (tidak ada logika bisnis)
- [ ] Daftarkan route `POST /api/v1/auth/login`
- [ ] Daftarkan route `POST /api/v1/auth/register`
- [ ] Daftarkan route `POST /api/v1/auth/forgot-password`
- [ ] Test semua endpoint Auth

---

### STEP 8 — Fitur User

- [ ] Buat `app/Repositories/V1/UserRepository.php`
- [ ] Buat `app/Services/V1/UserService.php`
- [ ] Buat `app/Http/Requests/V1/User/` — CreateUserRequest, UpdateUserRequest
- [ ] Buat `app/Http/Responses/V1/User/` — UserResponse, UserListResponse
- [ ] Buat `app/Http/Controllers/Api/V1/UserController.php`
- [ ] Daftarkan route CRUD `/api/v1/users` dengan middleware role `ADMIN`
- [ ] Test semua endpoint User

---

### STEP 9 — Fitur User Credit

- [ ] Buat Model `UserCredit`
- [ ] Buat `app/Repositories/V1/UserCreditRepository.php`
- [ ] Buat `app/Services/V1/UserCreditService.php`
- [ ] Buat `app/Http/Requests/V1/UserCredit/` — CreateUserCreditRequest, UpdateUserCreditRequest
- [ ] Buat `app/Http/Responses/V1/UserCredit/` — UserCreditResponse, UserCreditListResponse
- [ ] Buat `app/Http/Controllers/Api/V1/UserCreditController.php`
- [ ] Daftarkan route CRUD `/api/v1/user-credits` dengan middleware role `ADMIN`
- [ ] Test semua endpoint User Credit

---

### STEP 10 — Fitur Content

- [ ] Buat `app/Enums/ContentTypeEnum.php` — (`linkedin`, `x`, `thread`, `facebook`, `email_marketing`)
- [ ] Buat `app/Enums/ToneEnum.php` — (`formal`, `casual`, `persuasive`, dst)
- [ ] Buat Model `Content`
- [ ] Buat `app/Repositories/V1/ContentRepository.php`
- [ ] Buat `app/Services/V1/ContentService.php` (termasuk logika integrasi LLM & validasi kredit)
- [ ] Buat `app/Http/Requests/V1/Content/` — CreateContentRequest, UpdateContentRequest
- [ ] Buat `app/Http/Responses/V1/Content/` — ContentResponse, ContentListResponse
- [ ] Buat `app/Http/Controllers/Api/V1/ContentController.php`
- [ ] Daftarkan route CRUD `/api/v1/contents` dengan middleware role `ADMIN` & `MARIFATUN_USER`
- [ ] Test semua endpoint Content

---

### STEP 11 — Fitur Topup Request

- [ ] Buat `app/Enums/TopupStatusEnum.php` — (`pending`, `approved`, `rejected`)
- [ ] Buat `app/Enums/PaymentMethodEnum.php` — (`qris`)
- [ ] Buat Model `TopupRequest`
- [ ] Buat `app/Repositories/V1/TopupRequestRepository.php`
- [ ] Buat `app/Services/V1/TopupRequestService.php` (termasuk logika approval & penambahan kredit)
- [ ] Buat `app/Http/Requests/V1/TopupRequest/` — CreateTopupRequestRequest, UpdateTopupRequestRequest
- [ ] Buat `app/Http/Responses/V1/TopupRequest/` — TopupRequestResponse, TopupRequestListResponse
- [ ] Buat `app/Http/Controllers/Api/V1/TopupRequestController.php`
- [ ] Daftarkan route sesuai role:
  - `MARIFATUN_USER` → `POST`, `GET` `/api/v1/topup-requests`
  - `ADMIN` → `POST`, `GET`, `PUT`, `DELETE` `/api/v1/topup-requests`
- [ ] Test semua endpoint Topup Request

---

### STEP 12 — Fitur Dashboard

- [ ] Buat `app/Services/V1/DashboardService.php`
- [ ] Buat `app/Http/Responses/V1/Dashboard/` — AdminDashboardResponse, UserDashboardResponse
- [ ] Buat `app/Http/Controllers/Api/V1/DashboardController.php`
- [ ] Daftarkan route `GET /api/v1/dashboard/admin` dengan middleware role `ADMIN`
- [ ] Daftarkan route `GET /api/v1/dashboard/user` dengan middleware role `MARIFATUN_USER`
- [ ] Test semua endpoint Dashboard

---

### STEP 13 — Final Check

- [ ] Pastikan tidak ada logika bisnis di dalam Controller manapun
- [ ] Pastikan tidak ada duplikasi kode (terapkan reusable)
- [ ] Pastikan semua Enum menggunakan file di `app/Enums/`
- [ ] Pastikan semua tabel menggunakan UUID dan soft delete
- [ ] Pastikan Scramble men-generate dokumentasi dengan benar untuk semua endpoint
- [ ] Pastikan semua endpoint menggunakan prefix `v1/`
- [ ] Review seluruh Request & Response sudah terpisah per fungsi

---

## Deskripsi Sistem

Marifatun adalah platform berbasis API yang memungkinkan pengguna menghasilkan konten copywriting secara otomatis dengan bantuan LLM (Large Language Model). Sistem ini menggunakan mekanisme kredit berbayar sebagai berikut:

- **Per hari / Per satu user** → Diberikan akses **1 Kredit** secara gratis setiap harinya.
- **Per satu user** → Jika ingin menggunakan Fitur Contents, pengguna **wajib melakukan pembayaran** menggunakan **QRIS** sejumlah **Rp. 999**, setelah itu **Admin akan melakukan Approval** secara manual.

---

## Teknologi & Konfigurasi

- **Framework**: Laravel (PHP)
- **Arsitektur**: MVC (Model - View - Controller)
- **Database**: MySQL
- **Dokumentasi API**: Scramble
- **Role & Permission**: Spatie Laravel Permission

### Konfigurasi Database

| Key      | Value          |
|----------|----------------|
| Host     | localhost       |
| Username | root           |
| Password | pwdpwd8        |
| Database | db_marifatun   |

---

## Aturan & Konvensi Pengembangan

### Umum
- Gunakan **UUID** sebagai primary key (`id`) dengan tipe `string` di database.
- Gunakan **Soft Delete** pada hampir semua tabel.
- Gunakan **string** untuk tipe data Enum di database (bukan tipe `ENUM` native MySQL).
- Semua Enum didefinisikan di `App/Enums/NamaEnum.php`.
- Entah untuk status, role, atau apapun → gunakan Enum, simpan sebagai `string` di database.
- Terapkan **Reusable Code** — tidak ada duplikasi kode.

### Controller
- Tidak ada logika bisnis di dalam Controller.
- Setiap satu fungsi menerapkan **Request** dan **Response** tersendiri.

### Response
- Buat `BaseResponse` untuk single data.
- Buat `ListResponse` untuk banyak data dan pagination.
- Buat `ErrorResponse` untuk menangani error response.

### Endpoint & Versioning
- Semua endpoint dan foldering menggunakan prefix versi: `v1/`, `v2/`, `v3/`.

---

## Struktur Kolom Standar (Soft Delete)

Hampir semua tabel menggunakan struktur kolom berikut:

```ts
id?: string;
active?: boolean;
createdDate?: Date;
modifiedDate?: Date | null;
deletedDate?: Date | null;
createdBy?: string;
modifiedBy?: string | null;
deletedBy?: string | null;
```

---

## Struktur Folder

```
app/
 ├── Enums/
 ├── Http/
 │   ├── Controllers/
 │   │   └── Api/
 │   │       └── V1/
 │   ├── Requests/
 │   │   ├── base/
 │   │   └── V1/
 │   └── Responses/
 │       ├── base/
 │       └── V1/
 ├── Models/
 ├── Services/
 │   ├── base/
 │   └── V1/
 ├── Repositories/
 │   ├── base/
 │   └── V1/
 ├── Traits/
```

---

## Fitur

1. **User** — Manajemen data pengguna.
2. **User Credit** — Manajemen kredit pengguna (harian & pembelian).
3. **Content** — Generate konten copywriting dengan LLM.
4. **Topup Request** — Permintaan penambahan kredit melalui pembayaran QRIS.

---

## Endpoint API

### Auth
> Default Role: `MARIFATUN_USER`

| Method | Endpoint                  | Deskripsi                                                      |
|--------|---------------------------|----------------------------------------------------------------|
| POST   | `/api/v1/auth/register`   | Registrasi pengguna baru                                       |
| POST   | `/api/v1/auth/login`      | Login pengguna                                                 |
| POST   | `/api/v1/auth/forgot-password` | Forgot Password (kirim password baru ke email pengguna)   |

> Forgot Password menggunakan email yang tersimpan di database, kemudian sistem mengirimkan password baru ke email tersebut.

---

### User
> Role: `ADMIN`

| Method | Endpoint              | Deskripsi               |
|--------|-----------------------|-------------------------|
| POST   | `/api/v1/users`       | Create user             |
| PUT    | `/api/v1/users/{id}`  | Update user             |
| GET    | `/api/v1/users/{id}`  | View user               |
| DELETE | `/api/v1/users/{id}`  | Delete user             |

---

### User Credit
> Role: `ADMIN`

| Method | Endpoint                     | Deskripsi               |
|--------|------------------------------|-------------------------|
| POST   | `/api/v1/user-credits`       | Create user credit      |
| PUT    | `/api/v1/user-credits/{id}`  | Update user credit      |
| GET    | `/api/v1/user-credits/{id}`  | View user credit        |
| DELETE | `/api/v1/user-credits/{id}`  | Delete user credit      |

---

### Content
> Role: `ADMIN` dan `MARIFATUN_USER`

| Method | Endpoint                  | Deskripsi               |
|--------|---------------------------|-------------------------|
| POST   | `/api/v1/contents`        | Create content          |
| PUT    | `/api/v1/contents/{id}`   | Update content          |
| GET    | `/api/v1/contents/{id}`   | View content            |
| DELETE | `/api/v1/contents/{id}`   | Delete content          |

---

### Topup Request
> Role `MARIFATUN_USER`: CREATE, VIEW
> Role `ADMIN`: CREATE, VIEW, UPDATE, DELETE

| Method | Endpoint                       | Deskripsi                     | Role                          |
|--------|--------------------------------|-------------------------------|-------------------------------|
| POST   | `/api/v1/topup-requests`       | Buat permintaan topup         | `MARIFATUN_USER`              |
| GET    | `/api/v1/topup-requests`       | Lihat daftar topup request    | `MARIFATUN_USER`, `ADMIN`     |
| GET    | `/api/v1/topup-requests/{id}`  | Lihat detail topup request    | `MARIFATUN_USER`, `ADMIN`     |
| PUT    | `/api/v1/topup-requests/{id}`  | Update / Approval topup       | `ADMIN`                       |
| DELETE | `/api/v1/topup-requests/{id}`  | Hapus topup request           | `ADMIN`                       |

---

### Dashboard
> Role: `ADMIN` dan `MARIFATUN_USER`

| Method | Endpoint                      | Deskripsi                        |
|--------|-------------------------------|----------------------------------|
| GET    | `/api/v1/dashboard/admin`     | Dashboard untuk Role Admin       |
| GET    | `/api/v1/dashboard/user`      | Dashboard untuk Role User        |

---

## Database ERD

### Tabel: `users`

| Kolom        | Tipe    | Keterangan               |
|--------------|---------|--------------------------|
| id           | uuid    | Primary Key              |
| name         | string  |                          |
| email        | string  |                          |
| password     | string  |                          |
| active       | boolean |                          |
| createdDate  | datetime|                          |
| modifiedDate | datetime| nullable                 |
| deletedDate  | datetime| nullable (Soft Delete)   |
| createdBy    | string  |                          |
| modifiedBy   | string  | nullable                 |
| deletedBy    | string  | nullable                 |

---

### Tabel: `user_credits`

| Kolom            | Tipe     | Keterangan               |
|------------------|----------|--------------------------|
| id               | uuid     | Primary Key              |
| user_id          | uuid     | FK → users.id            |
| credits          | integer  |                          |
| last_daily_claim | datetime | nullable                 |
| active           | boolean  |                          |
| createdDate      | datetime |                          |
| modifiedDate     | datetime | nullable                 |
| deletedDate      | datetime | nullable (Soft Delete)   |
| createdBy        | string   |                          |
| modifiedBy       | string   | nullable                 |
| deletedBy        | string   | nullable                 |

---

### Tabel: `contents`

| Kolom           | Tipe    | Keterangan                                                      |
|-----------------|---------|-----------------------------------------------------------------|
| id              | uuid    | Primary Key                                                     |
| user_id         | uuid    | FK → users.id                                                   |
| content_type    | string  | Enum: `linkedin`, `x`, `thread`, `facebook`, `email_marketing` |
| topic           | string  |                                                                 |
| keywords        | text    |                                                                 |
| target_audience | string  |                                                                 |
| tone            | string  | Enum: misal `formal`, `casual`, `persuasive`, dsb              |
| result          | text    | Hasil generate dari LLM                                         |
| active          | boolean |                                                                 |
| createdDate     | datetime|                                                                 |
| modifiedDate    | datetime| nullable                                                        |
| deletedDate     | datetime| nullable (Soft Delete)                                          |
| createdBy       | string  |                                                                 |
| modifiedBy      | string  | nullable                                                        |
| deletedBy       | string  | nullable                                                        |

---

### Tabel: `topup_requests`

| Kolom          | Tipe     | Keterangan                                          |
|----------------|----------|-----------------------------------------------------|
| id             | uuid     | Primary Key                                         |
| user_id        | uuid     | FK → users.id                                       |
| amount         | decimal  | Nominal pembayaran (Rp. 999)                        |
| credits        | integer  | Jumlah kredit yang diminta                          |
| payment_method | string   | Enum: `qris`                                        |
| payment_proof  | string   | Path/URL bukti pembayaran                           |
| status         | string   | Enum: `pending`, `approved`, `rejected`             |
| approved_by    | uuid     | nullable, FK → users.id (Admin yang approve)        |
| approved_at    | datetime | nullable                                            |
| active         | boolean  |                                                     |
| createdDate    | datetime |                                                     |
| modifiedDate   | datetime | nullable                                            |
| deletedDate    | datetime | nullable (Soft Delete)                              |
| createdBy      | string   |                                                     |
| modifiedBy     | string   | nullable                                            |
| deletedBy      | string   | nullable                                            |

---

## Roles

| Role             | Deskripsi                              |
|------------------|----------------------------------------|
| `ADMIN`          | Akses penuh ke semua fitur manajemen   |
| `MARIFATUN_USER` | Akses fitur konten dan topup request   |

> Role dikelola menggunakan **Spatie Laravel Permission** dan disimpan sebagai `string` di database menggunakan Enum (`App/Enums/RoleEnum.php`).

---

## Dokumentasi API

Dokumentasi API di-generate secara otomatis menggunakan **Scramble**.

Akses dokumentasi di:
```
/docs/api
```

---

## Cara Instalasi

```bash
# 1. Clone repository
git clone <repository-url>
cd marifatun-backend

# 2. Install dependencies
composer install

# 3. Copy env
cp .env.example .env

# 4. Generate key
php artisan key:generate

# 5. Konfigurasi database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_marifatun
DB_USERNAME=root
DB_PASSWORD=pwdpwd8

# 6. Jalankan migrasi & seeder
php artisan migrate --seed

# 7. Jalankan server
php artisan serve
```

---

## Lisensi

Project ini dikembangkan untuk kebutuhan internal. Seluruh hak cipta dilindungi.