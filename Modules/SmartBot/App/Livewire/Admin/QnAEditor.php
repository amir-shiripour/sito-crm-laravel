<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Livewire\Admin;

use Livewire\Component;
use Modules\SmartBot\App\Models\BotQuestion;
use Modules\SmartBot\App\Models\BotAnswer;
use Modules\SmartBot\App\Models\BotMenuItem;

class QnAEditor extends Component
{
    // Form fields
    public ?int $editingQuestionId = null;
    public string $question_text = '';
    public string $keywords = '';
    public string $category_field = 'general';
    public int $priority = 0;
    public bool $is_active = true;

    // Answer fields
    public ?int $editingAnswerId = null;
    public string $answer_text = '';
    public string $answer_type = 'text'; // text, product_list, menu_items
    public array $selected_product_ids = [];
    public bool $show_add_to_cart = true;
    public array $smart_attachments = [];

    // Menu Item Drawer Fields
    public bool $isMenuItemDrawerOpen = false;
    public ?int $editingMenuItemId = null;
    public ?int $menuItemParentId = null;
    public string $menuItemLabel = '';
    public string $menuItemIcon = '';
    public int $menuItemSortOrder = 0;
    public string $menuItemResponseType = 'text'; // text, menu_items, product_list, url
    public string $menuItemResponseText = '';
    public array $menuItemResponseEntityIds = [];
    public string $menuItemResponseUrl = '';
    public bool $menuItemIsActive = true;
    public array $menuItemSmartAttachments = [];

    // Advanced Product Search & Filter fields
    public string $productSearchQuery = '';
    public string $productBrandId = '';
    public string $productCategoryId = '';
    public string $productDisplayCategoryId = '';

    public function mount(?int $id = null): void
    {
        if (!auth()->user()->can('smartbot.manage')) {
            abort(403);
        }

        if ($id) {
            $question = BotQuestion::with(['answers.rootMenuItems'])->findOrFail($id);
            $this->editingQuestionId = $question->id;
            $this->question_text = $question->question_text;
            $this->keywords = implode(', ', $question->keywords ?? []);
            $this->category_field = $question->category;
            $this->priority = $question->priority;
            $this->is_active = (bool) $question->is_active;

            $defaultAnswer = $question->defaultAnswer();
            if ($defaultAnswer) {
                $this->editingAnswerId = $defaultAnswer->id;
                $this->answer_text = $defaultAnswer->answer_text;
                $this->answer_type = $defaultAnswer->answer_type;
                $this->selected_product_ids = $defaultAnswer->entity_ids ?? [];
                $this->show_add_to_cart = (bool) $defaultAnswer->show_add_to_cart;
                $this->smart_attachments = $defaultAnswer->smart_attachments ?? [];
            }
        }
    }

    public function resetProductFilters(): void
    {
        $this->productSearchQuery = '';
        $this->productBrandId = '';
        $this->productCategoryId = '';
        $this->productDisplayCategoryId = '';
    }

    public function getBrandsProperty(): array
    {
        if (class_exists('\Modules\Market\Entities\Brand')) {
            return \Modules\Market\Entities\Brand::query()
                ->select('id', 'name')
                ->where('is_active', true)
                ->get()
                ->map(fn($b) => ['id' => $b->id, 'name' => $b->name])
                ->toArray();
        }
        return [];
    }

    public function getCategoriesProperty(): array
    {
        if (class_exists('\Modules\Market\Entities\Category')) {
            return \Modules\Market\Entities\Category::query()
                ->select('id', 'name')
                ->where('is_active', true)
                ->get()
                ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
                ->toArray();
        }
        return [];
    }

    public function getDisplayCategoriesProperty(): array
    {
        if (class_exists('\Modules\Market\Entities\DisplayCategory')) {
            return \Modules\Market\Entities\DisplayCategory::query()
                ->select('id', 'name')
                ->where('is_active', true)
                ->get()
                ->map(fn($dc) => ['id' => $dc->id, 'name' => $dc->name])
                ->toArray();
        }
        return [];
    }

    public function getFilteredProductsProperty(): array
    {
        if (!class_exists('\Modules\Market\Entities\MasterProduct')) {
            return [];
        }

        $query = \Modules\Market\Entities\MasterProduct::query()
            ->select('id', 'title', 'crm_code', 'gtin', 'barcode', 'brand_id', 'category_id', 'main_image')
            ->with(['brand:id,name', 'category:id,name'])
            ->where('status', 'active');

        if (!empty($this->productSearchQuery)) {
            $term = trim($this->productSearchQuery);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', '%' . $term . '%')
                  ->orWhere('id', 'like', '%' . $term . '%')
                  ->orWhere('crm_code', 'like', '%' . $term . '%')
                  ->orWhere('gtin', 'like', '%' . $term . '%')
                  ->orWhere('barcode', 'like', '%' . $term . '%');
            });
        }

        if (!empty($this->productBrandId)) {
            $query->where('brand_id', $this->productBrandId);
        }

        if (!empty($this->productCategoryId)) {
            $query->where('category_id', $this->productCategoryId);
        }

        if (!empty($this->productDisplayCategoryId)) {
            $query->whereHas('displayCategories', function ($q) {
                $q->where('display_category_id', $this->productDisplayCategoryId);
            });
        }

        return $query->limit(100)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'crm_code' => $p->crm_code,
                'gtin' => $p->gtin,
                'barcode' => $p->barcode,
                'brand_name' => $p->brand->name ?? null,
                'category_title' => $p->category->name ?? null,
                'image' => $p->main_image_url ?? null,
            ];
        })->toArray();
    }

    public function selectAllFilteredProducts(string $target = 'main'): void
    {
        $filtered = $this->filteredProducts;
        $filteredIds = array_map(fn($item) => (int)$item['id'], $filtered);

        if ($target === 'main') {
            $current = array_map('intval', $this->selected_product_ids);
            $this->selected_product_ids = array_values(array_unique(array_merge($current, $filteredIds)));
        } else {
            $current = array_map('intval', $this->menuItemResponseEntityIds);
            $this->menuItemResponseEntityIds = array_values(array_unique(array_merge($current, $filteredIds)));
        }
    }

    public function deselectAllProducts(string $target = 'main'): void
    {
        if ($target === 'main') {
            $this->selected_product_ids = [];
        } else {
            $this->menuItemResponseEntityIds = [];
        }
    }

    public function getProductsProperty(): array
    {
        return $this->filteredProducts;
    }

    public function getMenuItemsProperty(): array
    {
        if (!$this->editingAnswerId) {
            return [];
        }

        return BotMenuItem::where('answer_id', $this->editingAnswerId)
            ->whereNull('parent_item_id')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->with('children')
            ->get()
            ->toArray();
    }

    public function getParentItemLabelProperty(): ?string
    {
        if ($this->menuItemParentId) {
            return BotMenuItem::where('id', $this->menuItemParentId)->value('label');
        }
        return null;
    }

    public function getEditingMenuItemChildrenProperty(): array
    {
        if ($this->editingMenuItemId) {
            return BotMenuItem::where('parent_item_id', $this->editingMenuItemId)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();
        }
        return [];
    }

    public function save(bool $redirect = false): void
    {
        $this->validate([
            'question_text' => 'required|string|min:3',
            'keywords' => 'nullable|string',
            'category_field' => 'required|string',
            'priority' => 'required|integer|min:0',
            'answer_text' => 'required|string',
            'answer_type' => 'required|string|in:text,product_list,menu_items',
            'selected_product_ids' => 'required_if:answer_type,product_list|array',
        ]);

        $keywordsArray = array_filter(array_map('trim', explode(',', $this->keywords)));

        \DB::transaction(function () use ($keywordsArray) {
            $question = BotQuestion::updateOrCreate(
                ['id' => $this->editingQuestionId],
                [
                    'question_text' => $this->question_text,
                    'keywords' => $keywordsArray,
                    'category' => $this->category_field,
                    'priority' => $this->priority,
                    'is_active' => $this->is_active,
                    'created_by' => auth()->id(),
                ]
            );

            $this->editingQuestionId = $question->id;

            $answer = BotAnswer::updateOrCreate(
                ['question_id' => $question->id, 'is_default' => true],
                [
                    'answer_text' => $this->answer_text,
                    'answer_type' => $this->answer_type,
                    'entity_type' => $this->answer_type === 'product_list' ? 'market_product' : null,
                    'entity_ids' => $this->answer_type === 'product_list' ? $this->selected_product_ids : null,
                    'show_add_to_cart' => $this->show_add_to_cart,
                    'smart_attachments' => array_values($this->smart_attachments),
                ]
            );

            $this->editingAnswerId = $answer->id;
        });

        $this->dispatch('notify', type: 'success', text: 'اطلاعات سوال و جواب با موفقیت ذخیره شد.');

        if ($redirect) {
            session()->flash('success', 'سوال و جواب با موفقیت ذخیره شد.');
            $this->redirect(route('user.smartbot.qna'), navigate: true);
        }
    }

    public function addSmartAttachment(string $type, string $target = 'main'): void
    {
        $newItem = [
            'id' => 'att_' . uniqid(),
            'type' => $type,
            'card_number' => '',
            'bank_name' => '',
            'card_holder' => '',
            'iban_code' => '',
            'account_holder' => '',
            'network' => 'TRC20',
            'currency' => 'USDT',
            'address' => '',
            'button_label' => '',
            'button_url' => '',
            'button_style' => 'primary',
        ];

        if ($target === 'main') {
            $this->smart_attachments[] = $newItem;
        } else {
            $this->menuItemSmartAttachments[] = $newItem;
        }
    }

    public function removeSmartAttachment(int $index, string $target = 'main'): void
    {
        if ($target === 'main') {
            if (isset($this->smart_attachments[$index])) {
                array_splice($this->smart_attachments, $index, 1);
                $this->smart_attachments = array_values($this->smart_attachments);
            }
        } else {
            if (isset($this->menuItemSmartAttachments[$index])) {
                array_splice($this->menuItemSmartAttachments, $index, 1);
                $this->menuItemSmartAttachments = array_values($this->menuItemSmartAttachments);
            }
        }
    }

    public function openMenuItemDrawer(?int $itemId = null, ?int $parentId = null): void
    {
        if (!$this->editingAnswerId) {
            // First save question & answer if not saved yet
            $this->save(false);
        }

        $this->resetMenuItemForm();
        $this->menuItemParentId = $parentId;

        if ($itemId) {
            $item = BotMenuItem::findOrFail($itemId);
            $this->editingMenuItemId = $item->id;
            $this->menuItemParentId = $item->parent_item_id;
            $this->menuItemLabel = $item->label;
            $this->menuItemIcon = $item->icon ?? '';
            $this->menuItemSortOrder = $item->sort_order;
            $this->menuItemResponseType = $item->response_type;
            $this->menuItemResponseText = $item->response_text ?? '';
            $this->menuItemResponseEntityIds = $item->response_entity_ids ?? [];
            $this->menuItemResponseUrl = $item->response_url ?? '';
            $this->menuItemIsActive = (bool) $item->is_active;
            $this->menuItemSmartAttachments = $item->smart_attachments ?? [];
        }

        $this->isMenuItemDrawerOpen = true;
    }

    public function closeMenuItemDrawer(): void
    {
        $this->isMenuItemDrawerOpen = false;
        $this->resetMenuItemForm();
    }

    private function resetMenuItemForm(): void
    {
        $this->editingMenuItemId = null;
        $this->menuItemParentId = null;
        $this->menuItemLabel = '';
        $this->menuItemIcon = '';
        $this->menuItemSortOrder = 0;
        $this->menuItemResponseType = 'text';
        $this->menuItemResponseText = '';
        $this->menuItemResponseEntityIds = [];
        $this->menuItemResponseUrl = '';
        $this->menuItemIsActive = true;
        $this->menuItemSmartAttachments = [];
    }

    public function saveMenuItem(): void
    {
        if (!$this->editingAnswerId) {
            $this->dispatch('notify', type: 'error', text: 'ابتدا پاسخ اصلی را ذخیره کنید.');
            return;
        }

        $this->validate([
            'menuItemLabel' => 'required|string|max:255',
            'menuItemResponseType' => 'required|string|in:text,menu_items,product_list,url',
            'menuItemResponseText' => 'nullable|string',
            'menuItemResponseUrl' => 'nullable|url',
            'menuItemResponseEntityIds' => 'nullable|array',
            'menuItemSortOrder' => 'required|integer',
        ]);

        BotMenuItem::updateOrCreate(
            ['id' => $this->editingMenuItemId],
            [
                'answer_id' => $this->editingAnswerId,
                'parent_item_id' => $this->menuItemParentId,
                'label' => $this->menuItemLabel,
                'icon' => $this->menuItemIcon ?: null,
                'sort_order' => $this->menuItemSortOrder,
                'response_type' => $this->menuItemResponseType,
                'response_text' => $this->menuItemResponseText ?: null,
                'response_entity_ids' => $this->menuItemResponseType === 'product_list' ? $this->menuItemResponseEntityIds : null,
                'response_url' => $this->menuItemResponseType === 'url' ? $this->menuItemResponseUrl : null,
                'is_active' => $this->menuItemIsActive,
                'smart_attachments' => array_values($this->menuItemSmartAttachments),
            ]
        );

        $this->dispatch('notify', type: 'success', text: 'آیتم منو با موفقیت ذخیره شد.');
        $this->closeMenuItemDrawer();
    }

    public function deleteMenuItem(int $itemId): void
    {
        BotMenuItem::findOrFail($itemId)->delete();
        $this->dispatch('notify', type: 'success', text: 'آیتم منو و زیرمجموعه‌های آن حذف شدند.');
    }

    public function toggleMenuItemStatus(int $itemId): void
    {
        $item = BotMenuItem::findOrFail($itemId);
        $item->update(['is_active' => !$item->is_active]);
        $this->dispatch('notify', type: 'success', text: 'وضعیت آیتم منو تغییر کرد.');
    }

    public function render()
    {
        return view('smartbot::livewire.admin.qna-editor', [
            'brands' => $this->brands,
            'categories' => $this->categories,
            'displayCategories' => $this->displayCategories,
            'menuItems' => $this->menuItems,
        ])->layout('layouts.user', [
            'title' => $this->editingQuestionId ? 'ویرایش سوال و جواب دستیار هوشمند' : 'افزودن سوال و جواب جدید'
        ]);
    }
}
