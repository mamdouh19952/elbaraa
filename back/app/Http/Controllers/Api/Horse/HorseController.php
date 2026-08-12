<?php

namespace App\Http\Controllers\Api\Horse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Horse\StoreHorseRequest;
use App\Http\Requests\Horse\UpdateHorseRequest;
use App\Http\Resources\HorseResource;
use App\Http\Services\MediaService;
use App\Models\Horse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HorseController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    public function index(Request $request): JsonResponse
    {
        $horses = Horse::query()
            ->with(['categories', 'media'])
            ->inCategory($request->query('category'))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->latest()
            ->paginate($request->integer('per_page') ?: 12);

        return response()->json([
            'status' => true,
            'message' => 'Horses retrieved successfully',
            'data' => HorseResource::collection($horses)->response()->getData(true),
        ]);
    }

    public function show(Horse $horse): JsonResponse
    {
        $horse->load(['categories', 'media']);

        return response()->json([
            'status' => true,
            'message' => 'Horse retrieved successfully',
            'data' => new HorseResource($horse),
        ]);
    }

    public function store(StoreHorseRequest $request): JsonResponse
    {
        $horse = Horse::create($request->safe()->except(['categories', 'images', 'video']));

        $horse->categories()->sync($request->validated('categories') ?? []);

        if ($request->hasFile('images')) {
            $this->media->upload($horse, $request->file('images'), Horse::GALLERY_COLLECTION);
        }

        if ($request->hasFile('video')) {
            $this->media->upload($horse, $request->file('video'), Horse::VIDEO_COLLECTION);
        }

        $horse->load(['categories', 'media']);

        return response()->json([
            'status' => true,
            'message' => 'Horse created successfully',
            'data' => new HorseResource($horse),
        ], 201);
    }

    public function update(UpdateHorseRequest $request, Horse $horse): JsonResponse
    {
        $horse->update($request->safe()->except(['categories', 'images', 'video', 'removed_media_ids']));

        if ($request->has('categories')) {
            $horse->categories()->sync($request->validated('categories') ?? []);
        }

        if ($removed = $request->validated('removed_media_ids')) {
            $this->media->deleteMediaItems($horse, $removed, Horse::GALLERY_COLLECTION);
        }

        if ($request->hasFile('images')) {
            $this->media->upload($horse, $request->file('images'), Horse::GALLERY_COLLECTION);
        }

        // An uploaded video replaces any previous one — a horse has at most one.
        if ($request->hasFile('video')) {
            $this->media->update($horse, $request->file('video'), Horse::VIDEO_COLLECTION);
        }

        $horse->load(['categories', 'media']);

        return response()->json([
            'status' => true,
            'message' => 'Horse updated successfully',
            'data' => new HorseResource($horse->refresh()),
        ]);
    }

    public function destroy(Horse $horse): JsonResponse
    {
        $horse->delete();

        return response()->json([
            'status' => true,
            'message' => 'Horse deleted successfully',
        ]);
    }

    public function deleteImage(Horse $horse, int $mediaId): JsonResponse
    {
        $this->media->deleteMediaItem($horse, $mediaId, Horse::GALLERY_COLLECTION);

        $horse->load(['categories', 'media']);

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully',
            'data' => new HorseResource($horse),
        ]);
    }

    public function deleteVideo(Horse $horse): JsonResponse
    {
        $this->media->deleteMedia($horse, Horse::VIDEO_COLLECTION);
        $horse->update(['video_url' => null]);

        $horse->load(['categories', 'media']);

        return response()->json([
            'status' => true,
            'message' => 'Video removed successfully',
            'data' => new HorseResource($horse),
        ]);
    }
}
