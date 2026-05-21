<?php

namespace Modules\Accounting\App\Services;

use Modules\Accounting\App\Models\Category;

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
        $category->delete();
    }
}
