<?php

namespace App\Services\V1;

use App\Enums\PaymentMethodEnum;
use App\Enums\TopupStatusEnum;
use App\Models\TopupRequest;
use App\Repositories\V1\TopupRequestRepository;
use App\Services\base\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TopupRequestService extends BaseService
{
    public function __construct(
        TopupRequestRepository $repository,
        private readonly UserCreditService $creditService,
    ) {
        $this->repository = $repository;
    }

    public function create(array $data): TopupRequest
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        $data['payment_method'] = $data['payment_method'] ?? PaymentMethodEnum::QRIS->value;
        $data['status'] = TopupStatusEnum::PENDING->value;
        $data['amount'] = $data['amount'] ?? 999;
        $data['credits'] = $data['credits'] ?? 1;
        $data['active'] = true;

        return $this->repository->store($data);
    }

    public function update(string $id, array $data): TopupRequest
    {
        return DB::transaction(function () use ($id, $data) {
            $topup = $this->repository->findOrFail($id);
            $originalStatus = $topup->status;

            $payload = [];
            foreach (['amount', 'credits', 'payment_method', 'payment_proof', 'status', 'active'] as $k) {
                if (array_key_exists($k, $data)) {
                    $payload[$k] = $data[$k];
                }
            }

            $newStatus = $payload['status'] ?? $originalStatus;

            if ($newStatus === TopupStatusEnum::APPROVED->value && $originalStatus !== TopupStatusEnum::APPROVED->value) {
                $payload['approved_by'] = Auth::id();
                $payload['approved_at'] = now();

                $this->creditService->addCredits($topup->user_id, (int) ($payload['credits'] ?? $topup->credits));
            }

            if ($newStatus !== TopupStatusEnum::APPROVED->value) {
                $payload['approved_by'] = null;
                $payload['approved_at'] = null;
            }

            return $this->repository->update($id, $payload);
        });
    }
}
