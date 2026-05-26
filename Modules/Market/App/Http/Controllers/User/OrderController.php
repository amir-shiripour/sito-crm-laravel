<?php

namespace Modules\Market\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Market\App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            $orders = Order::with('client')->latest()->paginate(15);
        } else {
            $vendor = $user->marketVendor;
            if (!$vendor) {
                abort(403, 'شما پنل فروشندگی ندارید.');
            }
            $orders = Order::whereHas('items', function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id);
            })->with('client')->latest()->paginate(15);
        }

        return view('market::user.orders.index', compact('orders'));
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
}
