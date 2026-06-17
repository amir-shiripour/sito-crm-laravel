<?php

namespace Modules\Market\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Market\App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdOrSa = $user->hasRole('super-admin') || $user->hasRole('admin');

        $query = Order::query();
        if (!$isAdOrSa) {
            $vendor = $user->marketVendor;
            if (!$vendor) {
                abort(403, 'شما پنل فروشندگی ندارید.');
            }
            $query->whereHas('items', function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id);
            });
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhereHas('client', function($sub) use ($search) {
                      $sub->where('full_name', 'like', '%' . $search . '%')
                          ->orWhere('phone', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('market_order_status_id')) {
            $query->where('market_order_status_id', $request->input('market_order_status_id'));
        }

        // Compute total stats
        $statsQuery = Order::query();
        if (!$isAdOrSa) {
            $statsQuery->whereHas('items', function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id);
            });
        }

        $stats = [
            'total_count' => (clone $statsQuery)->count(),
            'total_revenue' => (clone $statsQuery)->where('payment_status', 'paid')->sum('grand_total'),
            'paid_count' => (clone $statsQuery)->where('payment_status', 'paid')->count(),
            'unpaid_count' => (clone $statsQuery)->where('payment_status', 'unpaid')->count(),
        ];

        $orders = $query->with('client')->latest()->paginate(15)->withQueryString();

        return view('market::user.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $user = auth()->user();
        $isAdOrSa = $user->hasRole('super-admin') || $user->hasRole('admin');
        $vendor = $user->marketVendor;

        if (!$isAdOrSa) {
            if (!$vendor) {
                abort(403, 'شما پنل فروشندگی ندارید.');
            }
            // Check if vendor has items in this order
            $hasItems = $order->items()->where('vendor_id', $vendor->id)->exists();
            if (!$hasItems) {
                abort(403, 'شما اجازه دسترسی به این سفارش را ندارید.');
            }
        }

        // Load items. For a vendor, only show their items. For admin, show all.
        $itemsQuery = $order->items();
        if (!$isAdOrSa) {
            $itemsQuery->where('vendor_id', $vendor->id);
        }
        $items = $itemsQuery->get();
        $order->setRelation('items', $items);

        return view('market::user.orders.show', compact('order'));
    }

    public function create()
    {
        $user = auth()->user();
        $isAdOrSa = $user->hasRole('super-admin') || $user->hasRole('admin');
        $vendor = $user->marketVendor;

        if (!$isAdOrSa && !$vendor) {
            abort(403, 'شما اجازه ثبت سفارش دستی را ندارید.');
        }

        return view('market::user.orders.create');
    }

    public function edit(Order $order)
    {
        $user = auth()->user();
        $isAdOrSa = $user->hasRole('super-admin') || $user->hasRole('admin');
        $vendor = $user->marketVendor;

        if (!$isAdOrSa) {
            if (!$vendor) {
                abort(403, 'شما پنل فروشندگی ندارید.');
            }
            // Check if vendor has items in this order
            $hasItems = $order->items()->where('vendor_id', $vendor->id)->exists();
            if (!$hasItems) {
                abort(403, 'شما اجازه دسترسی به این سفارش را ندارید.');
            }
        }

        return view('market::user.orders.edit', compact('order'));
    }

    public function destroy(Order $order)
    {
        $user = auth()->user();
        $isAdOrSa = $user->hasRole('super-admin') || $user->hasRole('admin');
        $vendor = $user->marketVendor;

        if (!$isAdOrSa) {
            if (!$vendor) {
                abort(403);
            }
            $hasItems = $order->items()->where('vendor_id', $vendor->id)->exists();
            if (!$hasItems) {
                abort(403);
            }
        }

        DB::transaction(function() use ($order) {
            // Restore stock before deleting
            try {
                app(\Modules\Market\App\Services\StockService::class)->releaseReservation($order);
            } catch (\Throwable $e) {
                \Log::error('Failed to release stock during manual order deletion: ' . $e->getMessage());
            }

            $order->items()->delete();
            $order->meta()->delete();
            $order->delete();
        });

        return redirect()->route('user.market.orders.index')->with('success', 'سفارش با موفقیت حذف شد و موجودی به انبارها بازگشت.');
    }
}
