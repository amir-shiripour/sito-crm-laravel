<?php

namespace Modules\FieldService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\FieldService\App\Services\RoutingService;

class RoutingApiController extends Controller
{
    public function getRoute(Request $request, RoutingService $service): JsonResponse
    {
        $validated = $request->validate([
            'origin_lat' => 'required|numeric',
            'origin_lng' => 'required|numeric',
            'dest_lat'   => 'required|numeric',
            'dest_lng'   => 'required|numeric',
        ]);

        $result = $service->getRoute(
            (float) $validated['origin_lat'],
            (float) $validated['origin_lng'],
            (float) $validated['dest_lat'],
            (float) $validated['dest_lng']
        );

        return response()->json($result);
    }
}
