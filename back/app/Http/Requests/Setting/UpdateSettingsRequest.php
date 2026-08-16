<?php

namespace App\Http\Requests\Setting;

use App\Http\Requests\ApiFormRequest;

class UpdateSettingsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'about_en' => 'nullable|string',
            'about_ar' => 'nullable|string',
            'about_zh' => 'nullable|string',
            'phone' => 'nullable|string|max:32',
            'whatsapp' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:255',
            'address_en' => 'nullable|string|max:255',
            'address_ar' => 'nullable|string|max:255',
            'address_zh' => 'nullable|string|max:255',
            'facebook' => 'nullable|url|max:2048',
            'instagram' => 'nullable|url|max:2048',
        ];
    }
}
