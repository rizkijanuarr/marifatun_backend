<?php

namespace App\Services\V1;

use App\Models\Content;
use App\Repositories\V1\ContentRepository;
use App\Services\base\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContentService extends BaseService
{
    public function __construct(
        ContentRepository $repository,
        private readonly UserCreditService $creditService,
        private readonly OpenRouterService $openRouter,
    ) {
        $this->repository = $repository;
    }

    public function create(array $data): Content
    {
        $userId = $data['user_id'] ?? Auth::id();

        return DB::transaction(function () use ($data, $userId) {
            $this->creditService->consumeCredit($userId, 1);

            $result = $this->openRouter->generateCopywriting([
                'content_type' => $data['content_type'],
                'topic' => $data['topic'],
                'keywords' => $data['keywords'] ?? '',
                'target_audience' => $data['target_audience'] ?? null,
                'tone' => $data['tone'],
            ]);

            return $this->repository->store([
                'user_id' => $userId,
                'content_type' => $data['content_type'],
                'topic' => $data['topic'],
                'keywords' => $data['keywords'] ?? null,
                'target_audience' => $data['target_audience'] ?? null,
                'tone' => $data['tone'],
                'result' => $result,
                'active' => true,
            ]);
        });
    }

    /**
     * Update konten berperilaku sebagai "revisi": sistem memanggil LLM
     * ulang untuk menghasilkan `result` baru berdasarkan brief yang dikirim.
     *
     * Aturan:
     * - Tidak mengonsumsi kredit.
     * - Maksimum {@see Content::MAX_REVISIONS} kali revisi per konten.
     *   Request ke-4 akan ditolak dengan ValidationException.
     */
    public function update(string $id, array $data): Content
    {
        /** @var Content $content */
        $content = $this->repository->find($id);

        if ($content->revision_count >= Content::MAX_REVISIONS) {
            throw ValidationException::withMessages([
                'revision' => [
                    'Batas revisi untuk konten ini telah tercapai ('
                    .Content::MAX_REVISIONS.'x). Silakan buat konten baru.',
                ],
            ]);
        }

        return DB::transaction(function () use ($content, $data) {
            $result = $this->openRouter->generateCopywriting([
                'content_type' => $data['content_type'],
                'topic' => $data['topic'],
                'keywords' => $data['keywords'] ?? '',
                'target_audience' => $data['target_audience'] ?? null,
                'tone' => $data['tone'],
            ]);

            return $this->repository->update($content->id, [
                'content_type' => $data['content_type'],
                'topic' => $data['topic'],
                'keywords' => $data['keywords'] ?? null,
                'target_audience' => $data['target_audience'] ?? null,
                'tone' => $data['tone'],
                'result' => $result,
                'revision_count' => $content->revision_count + 1,
            ]);
        });
    }
}
