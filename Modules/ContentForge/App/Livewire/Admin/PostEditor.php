<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\ContentForge\App\Models\ContentPost;
use Modules\ContentForge\App\Models\ContentEntity;
use Modules\ContentForge\App\Models\ContentCategory;
use Modules\ContentForge\App\Models\ContentTag;
use Modules\ContentForge\App\Enums\PostStatus;
use Modules\ContentForge\App\Enums\PostType;
use Modules\ContentForge\App\Enums\PostVisibility;
use Modules\ContentForge\App\Services\ContentAIService;
use App\Services\ImageOptimizerService;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostEditor extends Component
{
    use WithFileUploads, AuthorizesRequests;

    public ?ContentPost $post = null;
    public string $type = 'post'; // post | page
    public bool $isEdit = false;

    // Fields
    public int $entityId;
    public ?int $categoryId = null;
    public string $title = '';
    public string $slug = '';
    public string $excerpt = '';
    public string $body = ''; // Tiptap JSON or text
    public string $bodyHtml = '';
    public ?string $coverImage = null;
    public array $gallery = [];
    public string $themeKey = '';
    public string $status = 'draft';
    public string $visibility = 'public';
    public ?string $password = null;
    public ?string $publishedAt = null;
    public ?string $scheduledAt = null;
    public bool $featured = false;
    public bool $allowComments = true;
    public string $seoTitle = '';
    public string $seoDescription = '';
    public string $seoKeywords = '';
    public ?string $canonicalUrl = null;

    // Upload files temporary
    public $coverImageFile;
    public $galleryImageFile;

    // Selected Tags (Comma-separated or array)
    public string $tagsInput = '';

    // AI suggestion helpers
    public string $aiTopic = '';
    public bool $aiLoading = false;

    protected function rules(): array
    {
        return [
            'entityId'       => 'required|exists:content_entities,id',
            'categoryId'     => 'nullable|exists:content_categories,id',
            'title'          => 'required|string|max:255',
            'slug'           => 'nullable|string|max:255',
            'excerpt'        => 'nullable|string|max:1000',
            'body'           => 'nullable|string',
            'bodyHtml'       => 'nullable|string',
            'themeKey'       => 'nullable|string|max:100',
            'status'         => 'required|string',
            'visibility'     => 'required|string',
            'password'       => 'nullable|required_if:visibility,password|string|max:50',
            'publishedAt'    => 'nullable|date',
            'scheduledAt'    => 'nullable|date',
            'featured'       => 'boolean',
            'allowComments'  => 'boolean',
            'seoTitle'       => 'nullable|string|max:255',
            'seoDescription' => 'nullable|string|max:500',
            'seoKeywords'    => 'nullable|string|max:255',
            'canonicalUrl'   => 'nullable|url|max:255',
            'coverImageFile' => 'nullable|image|max:5120', // 5MB max
        ];
    }

    protected $validationAttributes = [
        'entityId' => 'موجودیت',
        'title'    => 'عنوان',
        'slug'     => 'نامک (URL)',
        'password' => 'رمز عبور صفحه',
    ];

    public function mount(?ContentPost $post = null, string $type = 'post'): void
    {
        $this->type = $type;

        if ($post && $post->exists) {
            $this->post = $post;
            $this->isEdit = true;
            $this->type = $post->type->value;
            $this->authorize('content.posts.edit', $post);

            // Populate fields
            $this->entityId = $post->entity_id;
            $this->categoryId = $post->category_id;
            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->excerpt = $post->excerpt ?? '';
            $this->body = $post->body ?? '';
            $this->bodyHtml = $post->body_html ?? '';
            $this->coverImage = $post->cover_image;
            $this->gallery = $post->gallery ?? [];
            $this->themeKey = $post->theme_key ?? '';
            $this->status = $post->status->value;
            $this->visibility = $post->visibility->value;
            $this->password = $post->password;
            $this->publishedAt = $post->published_at?->format('Y-m-d H:i');
            $this->scheduledAt = $post->scheduled_at?->format('Y-m-d H:i');
            $this->featured = $post->featured;
            $this->allowComments = $post->allow_comments;
            $this->seoTitle = $post->seo_title ?? '';
            $this->seoDescription = $post->seo_description ?? '';
            $this->seoKeywords = $post->seo_keywords ?? '';
            $this->canonicalUrl = $post->canonical_url;
            $this->tagsInput = implode(', ', $post->tags->pluck('name')->toArray());
        } else {
            $this->authorize('content.posts.create');
            $defaultEntity = ContentEntity::where('is_default', true)->first();
            $this->entityId = $defaultEntity ? (int)$defaultEntity->id : 0;
            if (!$this->entityId) {
                $firstEntity = ContentEntity::first();
                $this->entityId = $firstEntity ? (int)$firstEntity->id : 0;
            }
        }
    }

    public function updatedCoverImageFile(): void
    {
        $this->validateOnly('coverImageFile');

        $optimizer = app(ImageOptimizerService::class);
        $path = $optimizer->uploadAndOptimize(
            file: $this->coverImageFile,
            directory: "content/{$this->entityId}/covers",
            disk: 'public',
            maxWidth: 1600,
            quality: 82
        );

        $this->coverImage = $path;
        $this->coverImageFile = null;
        session()->flash('cover_success', 'تصویر کاور با موفقیت آپلود و بهینه شد.');
    }

    public function updatedGalleryImageFile(): void
    {
        $this->validate([
            'galleryImageFile' => 'required|image|max:5120',
        ]);

        $optimizer = app(ImageOptimizerService::class);
        $path = $optimizer->uploadAndOptimize(
            file: $this->galleryImageFile,
            directory: "content/{$this->entityId}/gallery",
            disk: 'public',
            maxWidth: 1920,
            quality: 80
        );

        $this->gallery[] = $path;
        $this->galleryImageFile = null;
        session()->flash('gallery_success', 'تصویر به گالری اضافه شد.');
    }

    public function removeGalleryImage(int $index): void
    {
        if (isset($this->gallery[$index])) {
            unset($this->gallery[$index]);
            $this->gallery = array_values($this->gallery);
        }
    }

    // --- AI assistant actions ---
    public function getAiService(): ContentAIService
    {
        return app(ContentAIService::class);
    }

    public function aiSuggestTitle(): void
    {
        if (empty($this->aiTopic)) {
            $this->addError('aiTopic', 'لطفاً موضوع را وارد کنید.');
            return;
        }

        $title = $this->getAiService()->suggestTitle($this->aiTopic);
        if ($title) {
            $this->title = $title;
            session()->flash('ai_success', 'عنوان تولید شد.');
        } else {
            session()->flash('ai_error', 'خطا در ارتباط با هوش مصنوعی.');
        }
    }

    public function aiGenerateExcerpt(): void
    {
        $text = strip_tags($this->bodyHtml);
        if (empty($text)) {
            session()->flash('ai_error', 'ابتدا باید محتوای متن را وارد کنید.');
            return;
        }

        $excerpt = $this->getAiService()->generateExcerpt($text);
        if ($excerpt) {
            $this->excerpt = $excerpt;
            session()->flash('ai_success', 'خلاصه تولید شد.');
        }
    }

    public function aiGenerateSeoDescription(): void
    {
        if (empty($this->title)) {
            session()->flash('ai_error', 'ابتدا باید عنوان مقاله را وارد کنید.');
            return;
        }

        $seoDesc = $this->getAiService()->generateSeoDescription($this->title, $this->excerpt);
        if ($seoDesc) {
            $this->seoDescription = $seoDesc;
            session()->flash('ai_success', 'توضیحات SEO تولید شد.');
        }
    }

    public function aiSuggestTags(): void
    {
        $text = strip_tags($this->bodyHtml);
        if (empty($text)) {
            session()->flash('ai_error', 'ابتدا باید محتوای متن را وارد کنید.');
            return;
        }

        $tags = $this->getAiService()->suggestTags($text);
        if ($tags) {
            $this->tagsInput = implode(', ', $tags);
            session()->flash('ai_success', 'برچسب‌ها پیشنهاد شدند.');
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'entity_id'       => $this->entityId,
            'category_id'     => $this->categoryId,
            'title'           => $this->title,
            'slug'            => $this->slug,
            'excerpt'         => $this->excerpt,
            'body'            => $this->body,
            'body_html'       => $this->bodyHtml,
            'cover_image'     => $this->coverImage,
            'gallery'         => $this->gallery,
            'theme_key'       => $this->themeKey,
            'status'          => $this->status,
            'visibility'      => $this->visibility,
            'password'        => $this->password,
            'published_at'    => $this->publishedAt ? new \DateTime($this->publishedAt) : null,
            'scheduled_at'    => $this->scheduledAt ? new \DateTime($this->scheduledAt) : null,
            'featured'        => $this->featured,
            'allow_comments'  => $this->allowComments,
            'seo_title'       => $this->seoTitle,
            'seo_description' => $this->seoDescription,
            'seo_keywords'    => $this->seoKeywords,
            'canonical_url'   => $this->canonicalUrl,
        ];

        if ($this->isEdit && $this->post) {
            // Check revision changes
            if ($this->post->body !== $this->body || $this->post->title !== $this->title) {
                \Modules\ContentForge\App\Models\ContentPostRevision::create([
                    'post_id'   => $this->post->id,
                    'user_id'   => auth()->id(),
                    'title'     => $this->post->title,
                    'body'      => $this->post->body,
                    'body_html' => $this->post->body_html,
                ]);
            }

            $this->post->update($data);
            $post = $this->post;
        } else {
            $data['author_id'] = auth()->id();
            $data['type'] = $this->type;
            $post = ContentPost::create($data);
        }

        // Sync tags
        $tags = array_unique(array_filter(array_map('trim', explode(',', $this->tagsInput))));
        $tagIds = [];
        foreach ($tags as $tagName) {
            $tag = ContentTag::firstOrCreate(
                ['entity_id' => $this->entityId, 'name' => $tagName],
                ['slug' => Str::slug($tagName, '-') ?: $tagName]
            );
            $tagIds[] = $tag->id;
        }
        $post->tags()->sync($tagIds);

        session()->flash('success', 'اطلاعات با موفقیت ذخیره شد.');

        return redirect()->route('user.content.' . ($this->type === 'post' ? 'posts' : 'pages') . '.index');
    }

    public function render()
    {
        $entities = ContentEntity::where('is_active', true)->get();
        $categories = ContentCategory::where('entity_id', $this->entityId)
            ->where('is_active', true)
            ->get();

        $revisions = $this->isEdit && $this->post
            ? $this->post->revisions()->with('user')->latest()->get()
            : collect([]);

        $aiAvailable = $this->getAiService()->isAvailable();

        return view('contentforge::livewire.admin.post-editor', compact('entities', 'categories', 'revisions', 'aiAvailable'))
            ->layout('layouts.user');
    }
}
