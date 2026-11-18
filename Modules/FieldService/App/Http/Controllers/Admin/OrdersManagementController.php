<?php

namespace Modules\FieldService\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Support\TechnicianCustomFieldsHelper;

class OrdersManagementController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        if (TechnicianCustomFieldsHelper::isTechnicianOnly($user)) {
            $technician = Technician::where('user_id', $user->id)->first();
            $query = ServiceOrder::where('technician_id', $technician?->id ?? 0)
                ->with(['client', 'category', 'invoice'])
                ->latest();

            if ($search = trim((string) $request->query('search', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%");
                });
            }

            $status = $request->query('status', 'all');
            if ($status !== 'all' && $status !== '') {
                $query->where('status', $status);
            }

            $orders = $query->paginate(15)->withQueryString();

            return view('fieldservice::technician.crm-orders', compact('orders', 'technician'));
        }

        return view('fieldservice::admin.orders-index');
    }
}
