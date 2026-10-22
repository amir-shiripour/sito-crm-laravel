<?php

namespace Modules\FieldService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\FieldService\App\Services\ReverseGeocodingService;

class ReverseGeocodeController extends Controller
{
    public function reverse(Request $request, ReverseGeocodingService $service): JsonResponse
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $result = $service->reverse((float) $validated['lat'], (float) $validated['lng']);

        return response()->json($result);
    }
}
