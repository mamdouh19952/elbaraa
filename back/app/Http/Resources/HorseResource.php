<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\NormalizesTranslations;
use App\Models\Horse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HorseResource extends JsonResource
{
    use NormalizesTranslations;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->translations('name'),
            'breed' => $this->translations('breed'),
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'description' => $this->translations('description'),
            'price' => $this->price !== null ? (float) $this->price : null,
            'currency' => $this->currency,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'cover_image' => $this->coverImage(),
            'images' => $this->images(),
            'video' => $this->video(),
        ];
    }

    private function coverImage(): ?string
    {
        return $this->getFirstMediaUrl(Horse::GALLERY_COLLECTION, 'thumb')
            ?: ($this->getFirstMediaUrl(Horse::GALLERY_COLLECTION) ?: null);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function images(): array
    {
        return $this->getMedia(Horse::GALLERY_COLLECTION)
            ->map(fn (Media $media) => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
            ])
            ->all();
    }

    /**
     * @return array<string, string>|null
     */
    private function video(): ?array
    {
        $uploaded = $this->getFirstMediaUrl(Horse::VIDEO_COLLECTION);

        if ($uploaded) {
            return ['type' => 'file', 'url' => $uploaded];
        }

        if ($this->video_url) {
            return ['type' => 'url', 'url' => $this->video_url];
        }

        return null;
    }
}
