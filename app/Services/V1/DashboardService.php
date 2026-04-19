<?php

namespace App\Services\V1;

use App\Enums\RoleEnum;
use App\Models\Content;
use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    public function adminSummary(): array
    {
        $totalUsers = User::count();
        $totalContents = Content::count();

        $contentsByType = Content::query()
            ->selectRaw('content_type, COUNT(*) as total')
            ->groupBy('content_type')
            ->pluck('total', 'content_type')
            ->toArray();

        $usersByRole = [
            [
                'role' => RoleEnum::ADMIN->value,
                'total' => User::role(RoleEnum::ADMIN->value)->count(),
            ],
            [
                'role' => RoleEnum::MARIFATUN_USER->value,
                'total' => User::role(RoleEnum::MARIFATUN_USER->value)->count(),
            ],
        ];

        $statusRows = Content::query()
            ->selectRaw('active, COUNT(*) as total')
            ->groupBy('active')
            ->get();

        $contentStatusBreakdown = ['active' => 0, 'inactive' => 0];
        foreach ($statusRows as $r) {
            $key = $r->active ? 'active' : 'inactive';
            $contentStatusBreakdown[$key] = (int) $r->total;
        }

        $toneRows = Content::query()
            ->selectRaw('tone, COUNT(*) as total')
            ->groupBy('tone')
            ->get();

        $toneMerged = [];
        foreach ($toneRows as $r) {
            $raw = $r->tone;
            $key = ($raw === null || trim((string) $raw) === '') ? 'unknown' : trim((string) $raw);
            $toneMerged[$key] = ($toneMerged[$key] ?? 0) + (int) $r->total;
        }
        arsort($toneMerged);
        $contentsByTone = [];
        foreach ($toneMerged as $tone => $total) {
            $contentsByTone[] = ['tone' => $tone, 'total' => $total];
        }

        $contentsCreatedByMonth = [];
        $start = Carbon::now()->startOfMonth()->subMonths(5);
        for ($i = 0; $i < 6; $i++) {
            $monthStart = $start->copy()->addMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();
            $contentsCreatedByMonth[] = [
                'month' => $monthStart->format('Y-m'),
                'label' => $monthStart->format('M Y'),
                'total' => Content::query()
                    ->whereBetween('createdDate', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
                    ->count(),
            ];
        }

        return [
            'total_users' => $totalUsers,
            'total_contents' => $totalContents,
            'contents_by_type' => $contentsByType,
            'users_by_role' => $usersByRole,
            'content_status_breakdown' => $contentStatusBreakdown,
            'contents_by_tone' => $contentsByTone,
            'contents_created_by_month' => $contentsCreatedByMonth,
        ];
    }

    /**
     * Ringkasan dashboard user: hanya konten **aktif** (`active = true`), selaras dengan list `GET /api/v1/user/contents`.
     */
    public function userSummary(string $userId): array
    {
        $totalContents = Content::where('user_id', $userId)->where('active', true)->count();

        $contentsByType = Content::query()
            ->where('user_id', $userId)
            ->where('active', true)
            ->selectRaw('content_type, COUNT(*) as total')
            ->groupBy('content_type')
            ->pluck('total', 'content_type')
            ->toArray();

        $recentContents = Content::where('user_id', $userId)
            ->where('active', true)
            ->orderByDesc('createdDate')
            ->limit(5)
            ->get()
            ->map(fn (Content $c) => [
                'id' => $c->id,
                'content_type' => $c->content_type,
                'topic' => $c->topic,
                'tone' => $c->tone,
                'createdDate' => optional($c->createdDate)->toIso8601String(),
            ])
            ->all();

        return [
            'total_contents' => $totalContents,
            'contents_by_type' => $contentsByType,
            'recent_contents' => $recentContents,
        ];
    }
}
