<?php

namespace App\Repositories\V1;

use App\Models\Content;
use App\Repositories\base\BaseRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ContentRepository extends BaseRepository
{
    public function __construct(Content $model)
    {
        parent::__construct($model);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->query();
        $this->applyContentScopeFilters($query, $filters);

        $query->with('user');
        /** List user (`user_id` di filter): urut `modifiedDate`; default desc. */
        if (! empty($filters['user_id'])) {
            $this->applyUserListOrdering($query, $filters);
        } else {
            $query->orderByDesc('contents.createdDate');
        }

        return $query->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateForAdmin(int $perPage, array $filters): LengthAwarePaginator
    {
        $query = $this->query();
        $this->applyContentScopeFilters($query, $filters);
        $this->applyAdminOrdering($query, $filters);

        return $query->with('user')->paginate($perPage);
    }

    /**
     * Total konten (sesuai filter) untuk dashboard admin — tanpa agregat per-user;
     * daftar utama sudah menampilkan semua baris dari semua pemilik.
     *
     * @param  array<string, mixed>  $filters
     * @return array{total_contents: int}
     */
    public function adminListStatistics(array $filters): array
    {
        $base = $this->query();
        $this->applyContentScopeFilters($base, $filters);

        return [
            'total_contents' => (clone $base)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function applyContentScopeFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['user_id'])) {
            $query->where('contents.user_id', $filters['user_id']);
        }

        if (! empty($filters['content_type'])) {
            $query->where('contents.content_type', $filters['content_type']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $includeUserSearch = (bool) ($filters['include_user_search'] ?? false);
            $includeToneInSearch = (bool) ($filters['include_tone_in_search'] ?? false);
            $query->where(function ($q) use ($search, $includeUserSearch, $includeToneInSearch) {
                $q->where('contents.topic', 'like', "%{$search}%")
                    ->orWhere('contents.keywords', 'like', "%{$search}%");
                if ($includeToneInSearch) {
                    $q->orWhere('contents.tone', 'like', "%{$search}%");
                }
                if ($includeUserSearch) {
                    $q->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('contents.createdDate', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('contents.createdDate', '<=', $filters['date_to']);
        }

        if (array_key_exists('active', $filters)) {
            $query->where('contents.active', (bool) $filters['active']);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function applyAdminOrdering(Builder $query, array $filters): void
    {
        $direction = strtolower((string) ($filters['sort_direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy('contents.modifiedDate', $direction);
    }

    /**
     * Urutan untuk daftar konten user: `modifiedDate` asc/desc (default desc).
     *
     * @param  array<string, mixed>  $filters
     */
    protected function applyUserListOrdering(Builder $query, array $filters): void
    {
        $direction = strtolower((string) ($filters['sort_direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy('contents.modifiedDate', $direction);
    }

    /**
     * Data agregat untuk chart dashboard user (pemilik konten).
     *
     * @return array{
     *     content_by_type: list<array{content_type: string, total: int}>,
     *     content_by_tone: list<array{tone: string, total: int}>,
     *     created_by_month: list<array{month: string, label: string, total: int}>,
     *     content_by_video_platform: list<array{platform: string, total: int}>,
     * }
     */
    public function userChartStatistics(string $userId): array
    {
        $byType = $this->query()
            ->where('contents.user_id', $userId)
            ->where('contents.active', true)
            ->selectRaw('contents.content_type, COUNT(*) as total')
            ->groupBy('contents.content_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'content_type' => (string) $r->content_type,
                'total' => (int) $r->total,
            ])
            ->values()
            ->all();

        $toneRows = $this->query()
            ->where('contents.user_id', $userId)
            ->where('contents.active', true)
            ->selectRaw('contents.tone, COUNT(*) as total')
            ->groupBy('contents.tone')
            ->get();

        $toneMerged = [];
        foreach ($toneRows as $r) {
            $raw = $r->tone;
            $key = ($raw === null || trim((string) $raw) === '') ? 'unknown' : trim((string) $raw);
            $toneMerged[$key] = ($toneMerged[$key] ?? 0) + (int) $r->total;
        }
        arsort($toneMerged);
        $byTone = [];
        foreach ($toneMerged as $tone => $total) {
            $byTone[] = ['tone' => $tone, 'total' => $total];
        }

        $platformRows = $this->query()
            ->where('contents.user_id', $userId)
            ->where('contents.active', true)
            ->where('contents.content_type', 'video_script')
            ->selectRaw('contents.video_platform, COUNT(*) as total')
            ->groupBy('contents.video_platform')
            ->get();

        $platformMerged = [];
        foreach ($platformRows as $r) {
            $raw = $r->video_platform;
            $key = ($raw === null || trim((string) $raw) === '') ? 'unknown' : trim((string) $raw);
            $platformMerged[$key] = ($platformMerged[$key] ?? 0) + (int) $r->total;
        }
        arsort($platformMerged);
        $byVideoPlatform = [];
        foreach ($platformMerged as $platform => $total) {
            $byVideoPlatform[] = ['platform' => $platform, 'total' => $total];
        }

        $createdByMonth = [];
        $start = Carbon::now()->startOfMonth()->subMonths(5);
        for ($i = 0; $i < 6; $i++) {
            $monthStart = $start->copy()->addMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();
            $monthKey = $monthStart->format('Y-m');
            $total = $this->query()
                ->where('contents.user_id', $userId)
                ->where('contents.active', true)
                ->whereBetween('contents.createdDate', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
                ->count();
            $createdByMonth[] = [
                'month' => $monthKey,
                'label' => $monthStart->format('M Y'),
                'total' => $total,
            ];
        }

        return [
            'content_by_type' => $byType,
            'content_by_tone' => $byTone,
            'created_by_month' => $createdByMonth,
            'content_by_video_platform' => $byVideoPlatform,
        ];
    }
}
