<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'entity_id'       => $this->entity_id,
            'parent_id'       => $this->parent_id,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'description'     => $this->description,
            'cover_image'     => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'theme_key'       => $this->theme_key,
            'seo' => [
                'title'       => $this->seo_title,
                'description' => $this->seo_description,
                'keywords'    => $this->seo_keywords,
                'canonical'   => $this->canonical_url,
            ],
            'sort_order'      => $this->sort_order,
            'is_active'       => $this->is_active,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
