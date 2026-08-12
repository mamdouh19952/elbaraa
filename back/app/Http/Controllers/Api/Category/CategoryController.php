<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'data' => CategoryResource::collection(Category::all()),
        ]);
    }
}
