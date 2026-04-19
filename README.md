# Marifatun Backend

<p align="center">
  <img src="./public/cover.png" alt="Marifatun — platform konten & scripting video" width="920" />
</p>

## Deskripsi

Marifatun merupakan kosakata yang diambil dari Arab yang mengartikan Pengetahuan, dengan tujuan untuk melakukan generating copywriting konten yang dapat di share di LinkedIn, X, Thread, Facebook, hingga ke Email Marketing. Dan juga melakukan scripting scenes untuk membuat Video.

## Instalasi

Pastikan di komputer lokal sudah terpasang:

- **Laravel** versi **v13**
- **PHP** versi **8.4**

Langkah menjalankan project:

```bash
cd marifatun-backend
composer install
php artisan key:generate
php artisan storage:link
php artisan db:seed
php artisan serv
```

Buka di browser: [http://localhost:8000/](http://localhost:8000/)
