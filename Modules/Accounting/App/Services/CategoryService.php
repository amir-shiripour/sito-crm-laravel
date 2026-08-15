<?php

namespace Modules\Accounting\App\Services;

use Modules\Accounting\App\Models\Category;
use Exception;

class CategoryService
{
    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);
        return $category;
    }

    public function deleteCategory(Category $category): void
    {
        if ($category->is_system) {
            throw new Exception("سرفصل سیستمی قابل حذف نیست.");
        }
        // TODO: Add check if category has associated transactions before deleting
        $category->delete();
    }
}
