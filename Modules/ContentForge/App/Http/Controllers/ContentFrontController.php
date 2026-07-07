<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ContentForge\App\Models\ContentEntity;
use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Models\ContentCategory;
use Modules\ContentForge\App\Models\ContentTag;
use Modules\ContentForge\App\Enums\PostStatus;
use Modules\ContentForge\App\Enums\PostVisibility;
use Modules\ContentForge\App\Services\ThemeResolver;
use Modules\ContentForge\Entities\ContentSetting;
use Illuminate\Support\Facades\Response;

final class ContentFrontController extends Controller
{
    protected ThemeResolver $themeResolver;

    public function __construct(ThemeResolver $themeResolver)
    {
        $this->themeResolver = $themeResolver;
    }

    protected function getEntity(string $entitySlug): ContentEntity
    {
        return ContentEntity::where('slug', $entitySlug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function archive(string $entitySlug, Request $request)
    {
        $entity = $this->getEntity($entitySlug);
        $perPage = (int) ContentSetting::getValue('general.posts_per_page', 12);

        $posts = ContentPost::published()
            ->where('entity_id', $entity->id)
            ->where('type', \Modules\ContentForge\App\Enums\PostType::Post)
            ->orderBy('sort_order')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);

        $view = $this->themeResolver->resolveForArchive($entity, 'archive');
        return view($view, compact('entity', 'posts'));
    }

    public function category(string $entitySlug, string $categorySlug, Request $request)
    {
        $entity = $this->getEntity($entitySlug);
        $category = ContentCategory::where('entity_id', $entity->id)
            ->where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $perPage = (int) ContentSetting::getValue('general.posts_per_page', 12);

        $posts = ContentPost::published()
            ->where('entity_id', $entity->id)
            ->where('category_id', $category->id)
            ->orderBy('sort_order')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);

        $view = $this->themeResolver->resolveForPost(new ContentPost(['entity_id' => $entity->id, 'category_id' => $category->id]), 'category');
        return view($view, compact('entity', 'category', 'posts'));
    }

    public function tag(string $entitySlug, string $tagSlug, Request $request)
    {
        $entity = $this->getEntity($entitySlug);
        $tag = ContentTag::where('entity_id', $entity->id)
            ->where('slug', $tagSlug)
            ->firstOrFail();

        $perPage = (int) ContentSetting::getValue('general.posts_per_page', 12);

        $posts = ContentPost::published()
            ->where('entity_id', $entity->id)
            ->whereHas('tags', fn($q) => $q->where('id', $tag->id))
            ->orderBy('sort_order')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);

        $view = $this->themeResolver->resolveForArchive($entity, 'tag');
        return view($view, compact('entity', 'tag', 'posts'));
    }

    public function show(string $entitySlug, string $slug, Request $request)
    {
        $entity = $this->getEntity($entitySlug);
        $post = ContentPost::where('entity_id', $entity->id)
            ->where('slug', $slug)
            ->whereIn('status', [PostStatus::Published, PostStatus::Archived])
            ->firstOrFail();

        // 1. Check Private visibility
        if ($post->visibility === PostVisibility::Private) {
            $this->middleware('auth');
            if (!auth()->check()) {
                abort(403, 'این صفحه خصوصی است و نیاز به ورود دارد.');
            }
        }

        // 2. Check Password visibility
        if ($post->visibility === PostVisibility::Password) {
            $sessionKey = "content_post_password_{$post->id}";
            if ($request->isMethod('post')) {
                $request->validate(['password' => 'required']);
                if ($request->password === $post->password) {
                    session([$sessionKey => true]);
                    return back();
                }
                return back()->withErrors(['password' => 'رمز عبور وارد شده اشتباه است.']);
            }

            if (!session($sessionKey)) {
                return view('contentforge::web.password_protected', compact('post', 'entity'));
            }
        }

        // Increment view count (safely using database transaction)
        $post->increment('view_count');

        $view = $this->themeResolver->resolveForPost($post, $post->type->value);
        return view($view, compact('entity', 'post'));
    }

    public function sitemap(string $entitySlug)
    {
        $entity = $this->getEntity($entitySlug);
        $posts = ContentPost::published()
            ->where('entity_id', $entity->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        $content = view('contentforge::web.sitemap', compact('entity', 'posts'))->render();
        return Response::make($content, 200, ['Content-Type' => 'application/xml']);
    }

    public function feed(string $entitySlug)
    {
        $entity = $this->getEntity($entitySlug);
        $posts = ContentPost::published()
            ->where('entity_id', $entity->id)
            ->where('type', \Modules\ContentForge\App\Enums\PostType::Post)
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        $content = view('contentforge::web.rss', compact('entity', 'posts'))->render();
        return Response::make($content, 200, ['Content-Type' => 'application/rss+xml']);
    }
}
