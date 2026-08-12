<?php

namespace App\Http\Controllers\Api\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\UpdateSettingsRequest;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Settings retrieved successfully',
            'data' => SiteSetting::allAsMap(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        SiteSetting::setMany($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Settings updated successfully',
            'data' => SiteSetting::allAsMap(),
        ]);
    }
}
