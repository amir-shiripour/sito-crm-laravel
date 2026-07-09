<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type->value,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'excerpt'        => $this->excerpt,
            'body'           => $this->body, // JSON (Tiptap block data)
            'body_html'      => $this->body_html,
            'cover_image'    => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'og_image'       => $this->og_image ? asset('storage/' . $this->og_image) : null,
            'gallery'        => is_array($this->gallery) ? array_map(fn($img) => asset('storage/' . $img), $this->gallery) : [],
            'attachments'    => is_array($this->attachments) ? array_map(fn($file) => asset('storage/' . $file), $this->attachments) : [],
            'theme_key'      => $this->theme_key,
            'status'         => $this->status->value,
            'visibility'     => $this->visibility->value,
            'short_code'     => $this->short_code,
            'view_count'     => $this->view_count,
            'reading_time'   => $this->reading_time,
            'featured'       => $this->featured,
            'allow_comments' => $this->allow_comments,
            'comment_count'  => $this->comment_count,
            'seo' => [
                'title'       => $this->seo_title,
                'description' => $this->seo_description,
                'keywords'    => $this->seo_keywords,
                'canonical'   => $this->canonical_url,
                'schema'      => $this->schema_markup,
            ],
            'published_at'   => $this->published_at?->toIso8601String(),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
            'author'         => [
                'id'         => $this->author_id,
                'name'       => $this->author->name ?? null,
                'email'      => $this->author->email ?? null,
            ],
            'category'       => new CategoryResource($this->whenLoaded('category')),
            'entity'         => new EntityResource($this->whenLoaded('entity')),
            'tags'           => $this->tags->pluck('name', 'slug')->toArray(),
        ];
    }
}
