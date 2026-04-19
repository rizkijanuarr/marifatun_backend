<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Content\CreateContentRequest;
use App\Http\Requests\V1\Content\ListContentRequest;
use App\Http\Requests\V1\Content\UpdateContentRequest;
use App\Http\Responses\base\BaseResponse;
use App\Http\Responses\V1\Content\ContentListResponse;
use App\Http\Responses\V1\Content\ContentResponse;
use App\Models\User;
use App\Services\V1\ContentService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('ROLE ADMIN', weight: 1)]
class ContentController extends Controller
{
    public function __construct(private readonly ContentService $service) {}

    public function index(Request $request): ContentListResponse
    {
        $listRequest = ListContentRequest::createFrom($request);
        $listRequest->setContainer(app());
        $listRequest->validateResolved();

        $result = $this->service->paginateListForAdmin(
            (int) $request->input('per_page', 15),
            $listRequest->filters(),
        );

        return ContentListResponse::fromPaginator(
            $result['paginator'],
            statistics: $result['statistics'],
        );
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

    public function show(string $content): ContentResponse
    {
        $model = $this->service->find($content);

        return ContentResponse::fromModel($model->load('user'));
    }

    public function update(UpdateContentRequest $request, string $content): ContentResponse
    {
        $updated = $this->service->update($content, $request->validated());

        return ContentResponse::fromModel($updated->load('user'), 'Konten berhasil diperbarui');
    }

    public function destroy(string $content): BaseResponse
    {
        $this->service->delete($content);

        return BaseResponse::make(null, 'Konten berhasil dinonaktifkan');
    }
}
