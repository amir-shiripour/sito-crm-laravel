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

    protected function getEntity(?string $entitySlug = null): ContentEntity
    {
        if (empty($entitySlug)) {
            return ContentEntity::where('is_default', true)->first() 
                ?? ContentEntity::where('is_active', true)->firstOrFail();
        }

        return ContentEntity::where('slug', $entitySlug)
            ->where('is_active', true)
            ->first() 
            ?? ContentEntity::where('is_default', true)->first()
            ?? ContentEntity::where('is_active', true)->firstOrFail();
    }

    // --- Core Show Method ---
    public function renderPost(ContentEntity $entity, ContentPost $post, Request $request)
    {
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

        // Increment view count
        $post->increment('view_count');

        $view = $this->themeResolver->resolveForPost($post, $post->type->value);
        return view($view, compact('entity', 'post'));
    }

    // --- Standard Actions ---

    public function archive(?string $entitySlug = null, ?Request $request = null)
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

    public function category(?string $entitySlug = null, ?string $categorySlug = null, ?Request $request = null)
    {
        $category = ContentCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $entity = $category->entity;
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

    public function tag(?string $entitySlug = null, ?string $tagSlug = null, ?Request $request = null)
    {
        $tag = ContentTag::where('slug', $tagSlug)->firstOrFail();
        $entity = $tag->entity;
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

        // SEO Redirect: If this slug belongs to the default entity, redirect permanently to the root
        if ($entity->is_default) {
            return redirect()->to(url('/' . $slug), 301);
        }

        $postQuery = ContentPost::where('entity_id', $entity->id)
            ->where('slug', $slug);

        // Allow previewing draft/scheduled posts for logged-in admins
        if (!auth()->check() || !auth()->user()->can('content.posts.view')) {
            $postQuery->whereIn('status', [PostStatus::Published, PostStatus::Archived]);
        }

        $post = $postQuery->firstOrFail();

        return $this->renderPost($entity, $post, $request);
    }

    public function sitemap(?string $entitySlug = null)
    {
        $entity = $this->getEntity($entitySlug);
        $posts = ContentPost::published()
            ->where('entity_id', $entity->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        $content = view('contentforge::web.sitemap', compact('entity', 'posts'))->render();
        return Response::make($content, 200, ['Content-Type' => 'application/xml']);
    }

    public function feed(?string $entitySlug = null)
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

    // --- Default Entity Root Route Handlers ---

    public function archiveDefault(Request $request)
    {
        return $this->archive(null, $request);
    }

    public function categoryDefault(string $categorySlug, Request $request)
    {
        return $this->category(null, $categorySlug, $request);
    }

    public function tagDefault(string $tagSlug, Request $request)
    {
        return $this->tag(null, $tagSlug, $request);
    }

    public function showDefault(string $slug, Request $request)
    {
        // 1. Try to find a post/page with this slug
        $postQuery = ContentPost::where('slug', $slug);

        // Allow previewing draft/scheduled posts for logged-in admins
        if (!auth()->check() || !auth()->user()->can('content.posts.view')) {
            $postQuery->whereIn('status', [PostStatus::Published, PostStatus::Archived]);
        }

        $post = $postQuery->first();

        if ($post) {
            $entity = $post->entity;
            return $this->renderPost($entity, $post, $request);
        }

        // 2. Try to find an active entity with this slug to show its blog archive
        $entity = ContentEntity::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($entity) {
            return $this->archive($entity->slug, $request);
        }

        // 3. Fallback: Not found
        abort(404);
    }

    public function sitemapDefault()
    {
        return $this->sitemap(null);
    }

    public function feedDefault()
    {
        return $this->feed(null);
    }
}
