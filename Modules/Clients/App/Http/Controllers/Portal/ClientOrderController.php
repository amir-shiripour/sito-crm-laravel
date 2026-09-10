<?php

namespace Modules\Clients\App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Services\App\Http\Models\Order;
use Modules\Services\App\Http\Models\Status;

class ClientOrderController extends Controller
{
    public function index(Request $request)
    {
        $client = auth('client')->user();

        if (!class_exists(Order::class) || !Schema::hasTable('service_orders')) {
            abort(404);
        }

        $query = Order::where('customer_id', $client->id)
            ->with(['service', 'status', 'invoice.status'])
            ->latest('id');

        if ($request->filled('status') && $request->status !== 'all') {
            $statusFilter = $request->status;
            if ($statusFilter === 'active') {
                $query->whereHas('status', function($q) {
                    $q->where('name', 'LIKE', '%فعال%')
                      ->where('name', 'NOT LIKE', '%غیر%');
                });
            } elseif ($statusFilter === 'pending') {
                $query->whereHas('status', function($q) {
                    $q->where('name', 'LIKE', '%انتظار%')
                      ->orWhere('name', 'LIKE', '%بررسی%');
                });
            } elseif ($statusFilter === 'canceled') {
                $query->whereHas('status', function($q) {
                    $q->where('name', 'LIKE', '%لغو%')
                      ->orWhere('name', 'LIKE', '%غیر فعال%');
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('service', function($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(12)->withQueryString();

        // Counts for tabs
        $totalCount = Order::where('customer_id', $client->id)->count();
        $activeCount = Order::where('customer_id', $client->id)
            ->whereHas('status', function($q) {
                $q->where('name', 'LIKE', '%فعال%')->where('name', 'NOT LIKE', '%غیر%');
            })->count();
        $pendingCount = Order::where('customer_id', $client->id)
            ->whereHas('status', function($q) {
                $q->where('name', 'LIKE', '%انتظار%')->orWhere('name', 'LIKE', '%بررسی%');
            })->count();

        return view('clients::portal.orders.services_index', compact(
            'orders',
            'totalCount',
            'activeCount',
            'pendingCount'
        ));
    }

    public function show($id)
    {
        $client = auth('client')->user();

        if (!class_exists(Order::class) || !Schema::hasTable('service_orders')) {
            abort(404);
        }

        $order = Order::where('customer_id', $client->id)
            ->with(['service.category', 'status', 'invoice.status', 'invoice.items', 'invoice.payments', 'customer'])
            ->findOrFail($id);

        return view('clients::portal.orders.services_show', compact('order'));
    }
}
