<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class EntityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'slug'                => $this->slug,
            'module_source'       => $this->module_source,
            'entity_reference_id' => $this->entity_reference_id,
            'theme_key'           => $this->theme_key,
            'settings'            => $this->settings,
            'is_default'          => $this->is_default,
            'is_active'           => $this->is_active,
            'created_at'          => $this->created_at?->toIso8601String(),
            'updated_at'          => $this->updated_at?->toIso8601String(),
        ];
    }
}
