<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\Tag;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                        ->setDescription('Bearer dari `POST /api/v1/auth/login`. Header: `Authorization: Bearer {token}`')
                );

                /** Urutan sidebar dokumentasi: Auth → ROLE ADMIN → ROLE MARIFATUN_USER */
                $openApi->tags = [
                    new Tag('Auth'),
                    new Tag('ROLE ADMIN'),
                    new Tag('ROLE MARIFATUN_USER'),
                ];

                $openApi->info->setDescription('');

                foreach ($openApi->paths as $path) {
                    foreach ($path->operations as $op) {
                        $op->description('');
                        $p = $path->path;
                        $route = '/api'.(str_starts_with($p, '/') ? $p : '/'.$p);
                        $op->summary(strtoupper($op->method).' '.$route);
                    }
                }
            });
    }
}
