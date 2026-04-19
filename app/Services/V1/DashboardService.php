<?php

namespace App\Services\V1;

use App\Enums\TopupStatusEnum;
use App\Models\Content;
use App\Models\TopupRequest;
use App\Models\User;
use App\Models\UserCredit;

class DashboardService
{
    public function adminSummary(): array
    {
        $totalUsers = User::count();
        $totalContents = Content::count();
        $totalTopupPending = TopupRequest::where('status', TopupStatusEnum::PENDING->value)->count();
        $totalTopupApproved = TopupRequest::where('status', TopupStatusEnum::APPROVED->value)->count();
        $totalTopupRejected = TopupRequest::where('status', TopupStatusEnum::REJECTED->value)->count();

        $monthlyRevenue = (float) TopupRequest::where('status', TopupStatusEnum::APPROVED->value)
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->sum('amount');

        $contentsByType = Content::query()
            ->selectRaw('content_type, COUNT(*) as total')
            ->groupBy('content_type')
            ->pluck('total', 'content_type')
            ->toArray();

        $latestTopups = TopupRequest::with('user')
            ->latest('createdDate')
            ->limit(5)
            ->get()
            ->map(fn (TopupRequest $t) => [
                'id' => $t->id,
                'user' => $t->user?->name,
                'amount' => (float) $t->amount,
                'credits' => (int) $t->credits,
                'status' => $t->status,
                'createdDate' => optional($t->createdDate)->toIso8601String(),
            ])
            ->all();

        return [
            'total_users' => $totalUsers,
            'total_contents' => $totalContents,
            'topup_summary' => [
                'pending' => $totalTopupPending,
                'approved' => $totalTopupApproved,
                'rejected' => $totalTopupRejected,
            ],
            'monthly_revenue' => $monthlyRevenue,
            'contents_by_type' => $contentsByType,
            'latest_topups' => $latestTopups,
        ];
    }

    public function userSummary(string $userId): array
    {
        $credit = UserCredit::where('user_id', $userId)->first();
        $totalContents = Content::where('user_id', $userId)->count();

        $topupHistory = TopupRequest::where('user_id', $userId)
            ->orderByDesc('createdDate')
            ->limit(10)
            ->get()
            ->map(fn (TopupRequest $t) => [
                'id' => $t->id,
                'amount' => (float) $t->amount,
                'credits' => (int) $t->credits,
                'status' => $t->status,
                'approved_at' => optional($t->approved_at)->toIso8601String(),
                'createdDate' => optional($t->createdDate)->toIso8601String(),
            ])
            ->all();

        $recentContents = Content::where('user_id', $userId)
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
            'credits' => [
                'balance' => $credit ? (int) $credit->credits : 0,
                'last_daily_claim' => $credit && $credit->last_daily_claim
                    ? $credit->last_daily_claim->toIso8601String()
                    : null,
                'can_claim_daily' => ! $credit || ! $credit->last_daily_claim || ! $credit->last_daily_claim->isSameDay(now()),
            ],
            'total_contents' => $totalContents,
            'recent_contents' => $recentContents,
            'topup_history' => $topupHistory,
        ];
    }
}
