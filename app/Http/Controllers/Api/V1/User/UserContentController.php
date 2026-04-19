<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Content\CreateContentRequest;
use App\Http\Requests\V1\Content\ListUserContentRequest;
use App\Http\Requests\V1\Content\UpdateContentRequest;
use App\Http\Responses\base\BaseResponse;
use App\Http\Responses\base\ErrorResponse;
use App\Http\Responses\V1\Content\ContentListResponse;
use App\Http\Responses\V1\Content\ContentResponse;
use App\Models\User;
use App\Services\V1\ContentService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('ROLE MARIFATUN_USER', weight: 2)]
class UserContentController extends Controller
{
    public function __construct(private readonly ContentService $service) {}

    public function statistics(Request $request): BaseResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $this->service->chartStatisticsForUser($user->id);

        return BaseResponse::make($data, 'Statistik konten', 200);
    }

    public function index(Request $request): ContentListResponse
    {
        $listRequest = ListUserContentRequest::createFrom($request);
        $listRequest->setContainer(app());
        $listRequest->validateResolved();

        /** @var User $user */
        $user = $request->user();

        $filters = array_merge($listRequest->filters(), [
            'user_id' => $user->id,
            'active' => true,
        ]);

        $paginator = $this->service->paginate(
            perPage: (int) $listRequest->input('per_page', 15),
            filters: $filters,
        );

        return ContentListResponse::fromPaginator($paginator);
    }

    public function store(CreateContentRequest $request): ContentResponse
    {
        /** @var User $user */
        $user = $request->user();

        $content = $this->service->create([
            ...$request->validated(),
            'user_id' => $user->id,
        ]);

        return ContentResponse::fromModel($content->load('user'), 'Konten berhasil dibuat', 201);
    }

    public function show(Request $request, string $content): ContentResponse|ErrorResponse
    {
        /** @var User $user */
        $user = $request->user();
        $model = $this->service->find($content);

        if ($model->user_id !== $user->id) {
            return ErrorResponse::make('Forbidden', 403, 'FORBIDDEN');
        }

        return ContentResponse::fromModel($model->load('user'));
    }

    public function update(UpdateContentRequest $request, string $content): ContentResponse|ErrorResponse
    {
        /** @var User $user */
        $user = $request->user();
        $existing = $this->service->find($content);

        if ($existing->user_id !== $user->id) {
            return ErrorResponse::make('Forbidden', 403, 'FORBIDDEN');
        }

        $updated = $this->service->update($content, $request->validated());

        return ContentResponse::fromModel($updated->load('user'), 'Konten berhasil diperbarui');
    }

    public function destroy(Request $request, string $content): BaseResponse|ErrorResponse
    {
        /** @var User $user */
        $user = $request->user();
        $existing = $this->service->find($content);

        if ($existing->user_id !== $user->id) {
            return ErrorResponse::make('Forbidden', 403, 'FORBIDDEN');
        }

        $this->service->delete($content);

        return BaseResponse::make(null, 'Konten berhasil dinonaktifkan');
    }
}
