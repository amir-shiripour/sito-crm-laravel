<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Livewire\Admin;

use Livewire\Component;
use Modules\ContentForge\App\Models\ContentCategory;
use Modules\ContentForge\App\Models\ContentEntity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryManager extends Component
{
    use AuthorizesRequests;

    public int $entityId;
    public ?int $parentId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public ?string $themeKey = null;
    public string $seoTitle = '';
    public string $seoDescription = '';
    public string $seoKeywords = '';

    public ?int $editingCategoryId = null;

    protected function rules(): array
    {
        return [
            'entityId'    => 'required|exists:content_entities,id',
            'parentId'    => 'nullable|exists:content_categories,id',
            'name'        => 'required|string|max:100',
            'slug'        => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'themeKey'    => 'nullable|string|max:50',
            'seoTitle'    => 'nullable|string|max:100',
            'seoDescription' => 'nullable|string|max:255',
            'seoKeywords' => 'nullable|string|max:255',
        ];
    }

    public function mount(): void
    {
        $this->authorize('content.categories.manage');
        $defaultEntity = ContentEntity::where('is_default', true)->first();
        $this->entityId = $defaultEntity ? (int)$defaultEntity->id : 0;
        if (!$this->entityId) {
            $firstEntity = ContentEntity::first();
            $this->entityId = $firstEntity ? (int)$firstEntity->id : 0;
        }
    }

    public function save(): void
    {
        if (empty($this->slug)) {
            $this->slug = \Modules\ContentForge\App\Services\SlugService::generate($this->name, 'content_categories', $this->editingCategoryId);
        }

        $this->validate();

        $data = [
            'entity_id'       => $this->entityId,
            'parent_id'       => $this->parentId,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'description'     => $this->description,
            'theme_key'       => $this->themeKey,
            'seo_title'       => $this->seoTitle,
            'seo_description' => $this->seoDescription,
            'seo_keywords'    => $this->seoKeywords,
        ];

        if ($this->editingCategoryId) {
            ContentCategory::findOrFail($this->editingCategoryId)->update($data);
            session()->flash('success', 'دسته‌بندی با موفقیت ویرایش شد.');
        } else {
            ContentCategory::create($data);
            session()->flash('success', 'دسته‌بندی جدید با موفقیت ایجاد شد.');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $category = ContentCategory::findOrFail($id);
        $this->editingCategoryId = $category->id;
        $this->entityId = $category->entity_id;
        $this->parentId = $category->parent_id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
        $this->themeKey = $category->theme_key;
        $this->seoTitle = $category->seo_title ?? '';
        $this->seoDescription = $category->seo_description ?? '';
        $this->seoKeywords = $category->seo_keywords ?? '';
    }

    public function delete(int $id): void
    {
        ContentCategory::findOrFail($id)->delete();
        session()->flash('success', 'دسته‌بندی حذف شد.');
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['editingCategoryId', 'parentId', 'name', 'slug', 'description', 'themeKey', 'seoTitle', 'seoDescription', 'seoKeywords']);
    }

    public function render()
    {
        $entities = ContentEntity::where('is_active', true)->get();
        
        $categories = ContentCategory::where('entity_id', $this->entityId)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $allCategories = ContentCategory::where('entity_id', $this->entityId)
            ->when($this->editingCategoryId, fn($q) => $q->where('id', '!=', $this->editingCategoryId))
            ->get();

        return view('contentforge::livewire.admin.category-manager', compact('entities', 'categories', 'allCategories'))
            ->layout('layouts.user');
    }
}
