<?php

namespace Modules\FieldService\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\FieldService\App\Enums\OrderStatus;
use Modules\FieldService\App\Enums\PaymentStatus;
use Modules\FieldService\App\Models\Building;
use Modules\FieldService\App\Models\PayaSettlement;
use Modules\FieldService\App\Models\ServiceInvoice;
use Modules\FieldService\App\Models\ServiceOrder;
use Modules\FieldService\App\Models\Technician;
use Modules\FieldService\App\Models\VendorPartner;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'online_technicians' => Technician::where('is_online', true)->count(),
            'total_technicians'  => Technician::count(),
            'active_orders'      => ServiceOrder::whereNotIn('status', [OrderStatus::COMPLETED, OrderStatus::CANCELLED])->count(),
            'today_orders'       => ServiceOrder::whereDate('created_at', today())->count(),
            'total_gmv'          => ServiceInvoice::whereIn('payment_status', [PaymentStatus::ESCROW_HELD, PaymentStatus::RELEASED])->sum('total_amount'),
            'escrow_pool'        => ServiceInvoice::where('payment_status', PaymentStatus::ESCROW_HELD)->sum('total_amount'),
            'platform_revenue'   => ServiceInvoice::whereIn('payment_status', [PaymentStatus::ESCROW_HELD, PaymentStatus::RELEASED])->sum('platform_commission_amount'),
            'pending_paya'       => PayaSettlement::where('status', 'pending')->sum('amount'),
            'buildings_count'    => Building::count(),
            'vendors_count'      => VendorPartner::count(),
        ];

        $recentOrders = ServiceOrder::with(['client', 'technician', 'category', 'invoice'])
            ->latest()
            ->take(8)
            ->get();

        $activeTechnicians = Technician::where('is_online', true)->latest()->take(6)->get();

        return view('fieldservice::admin.dashboard', compact('stats', 'recentOrders', 'activeTechnicians'));
    }
}
