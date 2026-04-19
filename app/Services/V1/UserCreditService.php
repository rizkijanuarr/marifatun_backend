<?php

namespace App\Services\V1;

use App\Models\UserCredit;
use App\Repositories\V1\UserCreditRepository;
use App\Services\base\BaseService;
use Illuminate\Support\Facades\DB;

class UserCreditService extends BaseService
{
    public function __construct(UserCreditRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): UserCredit
    {
        $data['active'] = $data['active'] ?? true;
        $data['credits'] = $data['credits'] ?? 0;

        return $this->repository->store($data);
    }

    public function claimDaily(string $userId): UserCredit
    {
        return DB::transaction(function () use ($userId) {
            $credit = $this->repository->findByUserId($userId);

            if (! $credit) {
                $credit = UserCredit::create([
                    'user_id' => $userId,
                    'credits' => 1,
                    'last_daily_claim' => now(),
                    'active' => true,
                ]);

                return $credit;
            }

            $now = now();
            if (! $credit->last_daily_claim || ! $credit->last_daily_claim->isSameDay($now)) {
                $credit->credits = ($credit->credits ?? 0) + 1;
                $credit->last_daily_claim = $now;
                $credit->save();
            }

            return $credit->refresh();
        });
    }

    public function addCredits(string $userId, int $amount): UserCredit
    {
        return DB::transaction(function () use ($userId, $amount) {
            $credit = $this->repository->findByUserId($userId);
            if (! $credit) {
                return UserCredit::create([
                    'user_id' => $userId,
                    'credits' => $amount,
                    'active' => true,
                ]);
            }

            $credit->credits = ($credit->credits ?? 0) + $amount;
            $credit->save();

            return $credit->refresh();
        });
    }

    public function consumeCredit(string $userId, int $amount = 1): UserCredit
    {
        return DB::transaction(function () use ($userId, $amount) {
            $credit = $this->repository->findByUserId($userId);
            if (! $credit || $credit->credits < $amount) {
                abort(402, 'Kredit tidak mencukupi. Silakan topup terlebih dahulu.');
            }

            $credit->credits -= $amount;
            $credit->save();

            return $credit->refresh();
        });
    }
}
