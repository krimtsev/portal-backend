<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Requests\Promotion\PromotionCreateRequest;
use App\Http\Requests\Promotion\PromotionUpdateRequest;
use App\Http\Resources\Promotion\PromotionResource;
use App\Models\Promotion\Promotion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Responses\JsonResponse;
use App\Http\Controllers\Controller;

class PromotionController extends Controller
{
    /**
     * Список промо-акций (с фильтрами и пагинацией).
     */
    /*public function list(Request $request): AnonymousResourceCollection
    {
        $promotions = Promotion::query()
            ->when(
                $request->boolean('visible'),
                fn ($q) => $q->visible()
            )
            ->when(
                $request->filled('q'),
                fn ($q) => $q->where('title', 'like', '%' . $request->string('q') . '%')
            )
            ->when(
                $request->filled('user_id'),
                fn ($q) => $q->where('user_id', $request->integer('user_id'))
            )
            ->with('user:id,name')
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return PromotionResource::collection($promotions);
    }*/

    public function get(Promotion $promotion): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'data' => new PromotionResource(
                $promotion->load([
                    'user:id,name,login',
                ])
            ),
        ]);
    }

    /**
     * Создать промо-акцию.
     */
    public function create(PromotionCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()?->id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('promotions', 'public');
        }

        Promotion::create($data);

        return JsonResponse::Created();
    }

    /**
     * Обновить промо-акцию.
     */
    public function update(PromotionUpdateRequest $request, Promotion $promotion): PromotionResource
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $this->deleteImage($promotion->image);
            $data['image'] = $request->file('image')->store('promotions', 'public');
        } elseif ($request->exists('image') && $request->input('image') === null) {

            $this->deleteImage($promotion->image);
            $data['image'] = null;
        }

        $promotion->update($data);

        return new PromotionResource($promotion->fresh()->load('user:id,name'));
    }

    /**
     * Удалить промо-акцию (soft delete + удаление файла).
     */
    /*public function remove(Promotion $promotion): Response
    {
        $this->deleteImage($promotion->image);
        $promotion->delete();

        return response()->noContent();
    }

    protected function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }*/
}
