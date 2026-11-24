<?php

namespace App\Http\Controllers\Promotion;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Promotion\PromotionCreateRequest;
use App\Http\Requests\Promotion\PromotionUpdateRequest;
use App\Http\Resources\Promotion\PromotionExportResource;
use App\Http\Resources\Promotion\PromotionListResource;
use App\Http\Resources\Promotion\PromotionPublicListResource;
use App\Http\Resources\Promotion\PromotionResource;
use App\Models\Promotion\Promotion;
use App\Responses\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PromotionController extends Controller
{
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Promotion::query()
            ->with([
                'user:id,name,login',
            ])
            ->select(
                'id',
                'title',
                'disabled',
                'expires_at',
                'created_at',
                'user_id'
            );

        $result = Pagination::paginate(
            $query,
            $request,
            [
                'title',
            ],
            ['title'],
            ['disabled'],
        );

        $result['list'] = PromotionListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    public function get(Request $request, Promotion $promotion): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'data' => new PromotionResource($promotion),
        ]);
    }

    public function create(PromotionCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->safe()->except([
            'image',
            'remove_image',
        ]);

        $data['user_id'] = $request->user()?->id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('', 'promotions');
        }

        Promotion::create($data);

        return JsonResponse::Created();
    }

    public function update(PromotionUpdateRequest $request, Promotion $promotion): \Illuminate\Http\JsonResponse
    {
        $data = $request->safe()->except([
            'image',
            'remove_image',
        ]);

        if ($request->hasFile('image')) {
            if ($promotion->image) {
                Storage::disk('promotions')->delete($promotion->image);
            }

            $data['image'] = $request->file('image')->store('', 'promotions');
        } elseif ($request->boolean('remove_image')) {
            if ($promotion->image) {
                Storage::disk('promotions')->delete($promotion->image);
            }
            $data['image'] = null;
        }

        $promotion->update($data);

        return JsonResponse::Updated();
    }

    public function remove(Promotion $promotion): \Illuminate\Http\JsonResponse
    {
        if ($promotion->image) {
            Storage::disk('promotions')->delete($promotion->image);
        }

        $promotion->delete();

        return JsonResponse::Removed();
    }

    protected function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function export(): array
    {
        $promotions = Promotion::query()
            ->with([
                'user:id,name',
            ])
            ->select(
                'id',
                'title',
                'description',
                'disabled',
                'expires_at',
                'user_id',
                'created_at',
            )
            ->orderBy('created_at')
            ->get();

        return PromotionExportResource::collection($promotions)->resolve();
    }

    public function image(Request $request, Promotion $promotion): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (!$promotion->image) {
            return JsonResponse::FileNotFound();
        }

        return redirect(Storage::disk('promotions')->url($promotion->image));
    }

    /**
     * Публичный список активных акций (для внешнего API).
     */
    public function publicList(): \Illuminate\Http\JsonResponse
    {
        $promotions = Promotion::query()
            ->visible()
            ->select(
                'title',
                'description',
                'created_at',
                'image'
            )
            ->orderByDesc('created_at')
            ->get();

        return JsonResponse::Send([
            'list' => PromotionPublicListResource::collection($promotions),
        ]);
    }
}
