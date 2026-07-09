<?php

declare(strict_types=1);

namespace Modules\ContentForge\App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\ContentForge\App\Models\ContentEntity;
use Modules\ContentForge\App\Http\Resources\EntityResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class EntityApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $entities = ContentEntity::where('is_active', true)->get();
        return EntityResource::collection($entities);
    }

    public function show(string $slug): EntityResource
    {
        $entity = ContentEntity::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return new EntityResource($entity);
    }
}
