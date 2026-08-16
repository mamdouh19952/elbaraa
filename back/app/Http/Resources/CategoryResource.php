<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\NormalizesTranslations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    use NormalizesTranslations;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->translations('name'),
        ];
    }
}
