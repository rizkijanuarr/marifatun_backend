<?php

namespace App\Services\V1;

use App\Enums\ContentTypeEnum;
use App\Models\Content;
use App\Repositories\V1\ContentRepository;
use App\Services\base\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContentService extends BaseService
{
    public function __construct(
        ContentRepository $repository,
        private readonly OpenRouterService $openRouter,
    ) {
        $this->repository = $repository;
    }

    /**
     * Statistik agregat untuk chart (dashboard user): tipe, nada, tren bulanan, platform skrip video.
     *
     * @return array<string, mixed>
     */
    public function chartStatisticsForUser(string $userId): array
    {
        /** @var ContentRepository $repo */
        $repo = $this->repository;

        return $repo->userChartStatistics($userId);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{paginator: LengthAwarePaginator, statistics: array<string, mixed>}
     */
    public function paginateListForAdmin(int $perPage, array $filters): array
    {
        /** @var ContentRepository $repo */
        $repo = $this->repository;

        return [
            'paginator' => $repo->paginateForAdmin($perPage, $filters),
            'statistics' => $repo->adminListStatistics($filters),
        ];
    }

    public function create(array $data): Content
    {
        $userId = $data['user_id'] ?? Auth::id();

        return DB::transaction(function () use ($data, $userId) {
            $isVideo = ($data['content_type'] ?? '') === ContentTypeEnum::VIDEO_SCRIPT->value;

            if ($isVideo) {
                $result = $this->openRouter->generateVideoScript([
                    'topic' => $data['topic'],
                    'target_audience' => $data['target_audience'] ?? null,
                    'tone' => $data['tone'],
                    'video_platform' => $data['video_platform'] ?? null,
                    'video_key_message' => $data['video_key_message'] ?? null,
                    'video_cta' => $data['video_cta'] ?? null,
                ]);
            } else {
                $result = $this->openRouter->generateCopywriting([
                    'content_type' => $data['content_type'],
                    'topic' => $data['topic'],
                    'keywords' => $data['keywords'] ?? '',
                    'target_audience' => $data['target_audience'] ?? null,
                    'tone' => $data['tone'],
                ]);
            }

            $videoFields = $isVideo
                ? [
                    'video_platform' => $data['video_platform'] ?? null,
                    'video_key_message' => $data['video_key_message'] ?? null,
                    'video_cta' => $data['video_cta'] ?? null,
                ]
                : [
                    'video_platform' => null,
                    'video_key_message' => null,
                    'video_cta' => null,
                ];

            return $this->repository->store(array_merge([
                'user_id' => $userId,
                'content_type' => $data['content_type'],
                'topic' => $data['topic'],
                'keywords' => $data['keywords'] ?? null,
                'target_audience' => $data['target_audience'] ?? null,
                'tone' => $data['tone'],
                'result' => $result,
                'active' => true,
            ], $videoFields));
        });
    }

    /**
     * Update konten: sistem memanggil LLM ulang untuk menghasilkan `result` baru
     * berdasarkan brief yang dikirim.
     */
    public function update(string $id, array $data): Content
    {
        /** @var Content $content */
        $content = $this->repository->find($id);

        return DB::transaction(function () use ($content, $data) {
            $isVideo = ($data['content_type'] ?? '') === ContentTypeEnum::VIDEO_SCRIPT->value;

            if ($isVideo) {
                $result = $this->openRouter->generateVideoScript([
                    'topic' => $data['topic'],
                    'target_audience' => $data['target_audience'] ?? null,
                    'tone' => $data['tone'],
                    'video_platform' => $data['video_platform'] ?? null,
                    'video_key_message' => $data['video_key_message'] ?? null,
                    'video_cta' => $data['video_cta'] ?? null,
                ]);
            } else {
                $result = $this->openRouter->generateCopywriting([
                    'content_type' => $data['content_type'],
                    'topic' => $data['topic'],
                    'keywords' => $data['keywords'] ?? '',
                    'target_audience' => $data['target_audience'] ?? null,
                    'tone' => $data['tone'],
                ]);
            }

            $videoFields = $isVideo
                ? [
                    'video_platform' => $data['video_platform'] ?? null,
                    'video_key_message' => $data['video_key_message'] ?? null,
                    'video_cta' => $data['video_cta'] ?? null,
                ]
                : [
                    'video_platform' => null,
                    'video_key_message' => null,
                    'video_cta' => null,
                ];

            return $this->repository->update($content->id, array_merge([
                'content_type' => $data['content_type'],
                'topic' => $data['topic'],
                'keywords' => $data['keywords'] ?? null,
                'target_audience' => $data['target_audience'] ?? null,
                'tone' => $data['tone'],
                'result' => $result,
            ], $videoFields));
        });
    }

    /**
     * Menonaktifkan konten (active = false), bukan soft delete.
     */
    public function delete(string $id): bool
    {
        $this->repository->update($id, ['active' => false]);

        return true;
    }
}
