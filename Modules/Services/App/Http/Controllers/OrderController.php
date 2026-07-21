<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Services\App\Http\Models\Order;
use Modules\Services\App\Http\Models\Status;
use Modules\Settings\Entities\Setting;
use Morilog\Jalali\Jalalian;
use Modules\Clients\Entities\Client;
use Modules\Services\App\Http\Models\Service;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Order::class);

        $query = Order::with(['customer', 'status', 'service', 'invoice.payments'])
            ->when($request->search, function ($q, $s) {
                $q->where('order_number', 'like', "%$s%")
                    ->orWhere('client_name', 'like', "%$s%")
                    ->orWhereHas('service', function ($q) use ($s) {
                        $q->where('name', 'like', "%$s%");
                    })
                    ->orWhereHas('invoice', function ($q) use ($s) {
                        $q->where('invoice_number', 'like', "%$s%")
                            ->orWhere('proforma_invoice_number', 'like', "%$s%");
                    });
            })
            ->when($request->status_id, function ($q, $v) {
                $q->where('status_id', $v);
            })
            ->when($request->service_id, function ($q, $v) {
                $q->where('service_id', $v);
            })
            ->when($request->customer_id, function ($q, $v) {
                $q->where('customer_id', $v);
            });

        $orders = $query->orderBy('invoice_id', 'desc')->orderBy('id', 'asc')->paginate(20)->withQueryString();

        $statuses = Status::where('type', 'order')->orderBy('sort_order')->get();
        $services = Service::orderBy('name')->get();
        $customers = Client::orderBy('full_name')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::orders.index', compact('orders', 'statuses', 'services', 'customers', 'currency'));
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'status', 'service', 'invoice.payments']);

        $statuses = Status::where('type', 'order')->orderBy('sort_order')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::orders.show', compact('order', 'statuses', 'currency'));
    }

    public function update(Request $request, Order $order)
    {
        if ($request->has('renewal_price')) {
            $request->merge(['renewal_price' => str_replace(',', '', $request->renewal_price)]);
        }

        $validated = $request->validate([
            'status_id' => 'nullable|exists:services_statuses,id',
            'renewal_date' => 'nullable|string',
            'renewal_price_type' => 'required|in:auto,manual',
            'renewal_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($request->filled('renewal_date')) {
            try {
                $englishDate = str_replace(
                    ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
                    ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                    $request->renewal_date
                );
                $validated['renewal_date'] = Jalalian::fromFormat('Y/m/d', $englishDate)->toCarbon();
            } catch (\Exception $e) {
                unset($validated['renewal_date']);
            }
        }

        if ($validated['renewal_price_type'] === 'auto') {
            $validated['renewal_price'] = $order->renewal_price;
        }

        $order->update($validated);

        return back()->with('success', 'اطلاعات سفارش با موفقیت بروزرسانی شد.');
    }
    public function create()
    {
        return redirect()->route('services.invoices.create', ['type' => 'invoice']);
    }

    public function store(Request $request)
    {
    }

    public function edit($id)
    {
    }

    public function destroy($id)
    {
    }
}
