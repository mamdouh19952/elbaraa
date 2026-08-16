<?php

namespace App\Http\Requests\Horse;

use App\Http\Requests\ApiFormRequest;

class StoreHorseRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'name.zh' => 'nullable|string|max:255',

            'breed' => 'nullable|array',
            'breed.ar' => 'nullable|string|max:255',
            'breed.en' => 'nullable|string|max:255',
            'breed.zh' => 'nullable|string|max:255',

            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date|before_or_equal:today',

            'description' => 'nullable|array',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'description.zh' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'status' => 'required|in:available,reserved,sold',
            'is_featured' => 'nullable|boolean',

            'video_url' => 'nullable|url|max:2048',
            'video' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:51200',

            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',

            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }
}
