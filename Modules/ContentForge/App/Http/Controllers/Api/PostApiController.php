<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PostApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ContentPost::published()
            ->with(['author', 'category', 'entity', 'tags']);

        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        $posts = $query->orderBy('sort_order')
            ->orderBy('published_at', 'desc')
            ->paginate((int)$request->get('per_page', 12));

        return PostResource::collection($posts);
    }

    public function show(string $slug): PostResource
    {
        $post = ContentPost::where('slug', $slug)
            ->published()
            ->with(['author', 'category', 'entity', 'tags'])
            ->firstOrFail();

        return new PostResource($post);
    }
}
