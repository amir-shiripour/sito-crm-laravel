<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;

/*
|--------------------------------------------------------------------------
| API Routes for FieldService
|--------------------------------------------------------------------------
*/

Route::prefix('fieldservice')->group(function () {
    // Technician GPS heartbeat & status
    Route::post('/technician/location', function (Request $request) {
        $validated = $request->validate([
            'technician_id' => 'required|exists:field_service_technicians,id',
            'lat'           => 'required|numeric',
            'lng'           => 'required|numeric',
        ]);

        $tech = Technician::find($validated['technician_id']);
        if ($tech) {
            $tech->lat = $validated['lat'];
            $tech->lng = $validated['lng'];
            $tech->last_active_at = now();
            $tech->save();
        }

        return response()->json(['success' => true]);
    });

    // Order status poll
    Route::get('/orders/{orderNumber}/status', function (string $orderNumber) {
        $order = ServiceOrder::where('order_number', $orderNumber)
            ->with(['technician', 'invoice'])
            ->first();

        if (!$order) {
            return response()->json(['error' => 'سفارش یافت نشد.'], 404);
        }

        return response()->json([
            'order_number' => $order->order_number,
            'status'       => $order->status->value,
            'status_label' => $order->status->label(),
            'technician'   => $order->technician ? [
                'name'   => $order->technician->full_name,
                'mobile' => $order->technician->mobile,
                'lat'    => $order->technician->lat,
                'lng'    => $order->technician->lng,
                'rating' => $order->technician->rating_avg,
            ] : null,
            'invoice'      => $order->invoice ? [
                'total'  => $order->invoice->total_amount,
                'status' => $order->invoice->payment_status->value,
            ] : null,
        ]);
    });

    // High-performance stateless reverse geocode API
    Route::get('/reverse-geocode', [\Modules\FieldService\App\Http\Controllers\ReverseGeocodeController::class, 'reverse']);
});
