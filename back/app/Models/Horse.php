<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Horse extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const GALLERY_COLLECTION = 'horse_gallery';

    public const VIDEO_COLLECTION = 'horse_video';

    protected $fillable = [
        'name_en',
        'name_ar',
        'breed_en',
        'breed_ar',
        'gender',
        'date_of_birth',
        'description_en',
        'description_ar',
        'price',
        'currency',
        'status',
        'video_url',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(600)
            ->height(400)
            ->nonQueued();
    }

    public function scopeInCategory(Builder $query, ?string $slug): Builder
    {
        return $query->when($slug, fn (Builder $q) => $q->whereHas(
            'categories',
            fn (Builder $c) => $c->where('slug', $slug)
        ));
    }
}
