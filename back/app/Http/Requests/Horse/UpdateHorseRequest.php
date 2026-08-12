<?php

namespace App\Http\Requests\Horse;

use App\Http\Requests\ApiFormRequest;

class UpdateHorseRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name_en' => 'sometimes|required|string|max:255',
            'name_ar' => 'sometimes|required|string|max:255',
            'breed_en' => 'nullable|string|max:255',
            'breed_ar' => 'nullable|string|max:255',
            'gender' => 'sometimes|required|in:male,female',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'status' => 'sometimes|required|in:available,reserved,sold',
            'is_featured' => 'nullable|boolean',

            'video_url' => 'nullable|url|max:2048',
            'video' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:51200',

            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',

            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',

            'removed_media_ids' => 'nullable|array',
            'removed_media_ids.*' => 'integer',
        ];
    }
}
