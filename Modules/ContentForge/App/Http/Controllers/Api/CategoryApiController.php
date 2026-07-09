<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\ContentForge\App\Models\ContentCategory;
use Modules\ContentForge\App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CategoryApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ContentCategory::where('is_active', true);
        
        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }
        
        $categories = $query->orderBy('sort_order')->get();
        return CategoryResource::collection($categories);
    }

    public function show(string $slug): CategoryResource
    {
        $category = ContentCategory::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return new CategoryResource($category);
    }
}
