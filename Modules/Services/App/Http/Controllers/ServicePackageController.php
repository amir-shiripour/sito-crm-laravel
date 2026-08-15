<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Services\App\Http\Models\Service;
use Modules\Services\App\Http\Models\ServicePackage;
use Modules\Services\App\Http\Models\ServicePackageItem;
use Modules\Services\App\Http\Requests\StoreServicePackageRequest;
use Modules\Settings\Entities\Setting;

class ServicePackageController extends Controller
{
    private function parsePrice($val)
    {
        if (empty($val)) return 0;
        if (is_numeric($val)) return floatval($val);

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $val = str_replace($persian, $english, (string)$val);
        $val = str_replace($arabic, $english, $val);

        $clean = preg_replace('/[^\d.]/', '', $val);

        return floatval($clean);
    }

    public function index(Request $request)
    {
        $packages = ServicePackage::withCount('items')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::packages.index', compact('packages', 'currency'));
    }

    public function create()
    {
        $services = Service::active()->with('customFields')->orderBy('name')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::packages.create', compact('services', 'currency'));
    }

    public function store(StoreServicePackageRequest $request)
    {
        DB::transaction(function () use ($request, &$package) {
            $itemsData = $request->input('items', []);
            $subtotal = 0;

            $processedItems = [];

            foreach ($itemsData as $item) {
                $qty = floatval($this->parsePrice($item['quantity'] ?? 1));
                if ($qty <= 0) $qty = 1;
                $unitPrice = intval($this->parsePrice($item['unit_price'] ?? 0));
                $discType = $item['discount_type'] ?? 'amount';
                $discVal = floatval($this->parsePrice($item['discount_value'] ?? 0));

                $customFields = $item['custom_fields'] ?? [];
                $rawCustomFieldsPrices = $item['custom_fields_prices'] ?? [];
                $customFieldsPrices = [];

                foreach ($rawCustomFieldsPrices as $k => $v) {
                    $customFieldsPrices[$k] = $this->parsePrice($v);
                }

                $cfSubtotal = 0;
                if (!empty($item['service_id'])) {
                    $service = Service::with('customFields')->find($item['service_id']);
                    if ($service && !empty($service->customFields)) {
                        foreach ($service->customFields as $cf) {
                            $val = $customFields[$cf->id] ?? null;
                            $isSelected = false;
                            if ($val !== null && $val !== '') {
                                if ($cf->type === 'checkbox') {
                                    $isSelected = !empty($val);
                                } elseif ($cf->type === 'multiselect') {
                                    $isSelected = is_array($val) && count($val) > 0;
                                } else {
                                    $isSelected = true;
                                }
                            }
                            if ($cf->has_pricing && $isSelected) {
                                $cfPrice = 0;
                                if (isset($customFieldsPrices[$cf->id]) && $customFieldsPrices[$cf->id] > 0) {
                                    $cfPrice = floatval($customFieldsPrices[$cf->id]);
                                } else {
                                    $cfAmount = floatval($cf->pricing_amount ?? 0);
                                    $cfPrice = ($cf->pricing_type === 'percentage') ? ($unitPrice * ($cfAmount / 100)) : $cfAmount;
                                    $customFieldsPrices[$cf->id] = $cfPrice;
                                }
                                $cfSubtotal += ($cfPrice * $qty);
                            }
                        }
                    }
                }

                $rowSubtotal = ($qty * $unitPrice) + $cfSubtotal;
                if ($discType === 'percent') {
                    $discAmount = round(($rowSubtotal * min(100, max(0, $discVal))) / 100);
                } else {
                    $discAmount = min($rowSubtotal, $discVal);
                }

                $rowTotal = max(0, $rowSubtotal - $discAmount);
                $subtotal += $rowTotal;

                $processedItems[] = [
                    'service_id' => !empty($item['service_id']) ? $item['service_id'] : null,
                    'custom_service_name' => $item['custom_service_name'] ?? '',
                    'description' => $item['description'] ?? '',
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'عدد',
                    'unit_price' => $unitPrice,
                    'discount_type' => $discType,
                    'discount_value' => $discVal,
                    'discount_amount' => $discAmount,
                    'billing_period' => $item['billing_period'] ?? null,
                    'custom_fields' => $customFields,
                    'custom_fields_prices' => $customFieldsPrices,
                    'total_price' => $rowTotal,
                ];
            }

            $packageDiscType = $request->input('discount_type', 'amount');
            $packageDiscVal = floatval($this->parsePrice($request->input('discount_value', 0)));

            if ($packageDiscType === 'percent') {
                $packageDiscAmount = round(($subtotal * min(100, max(0, $packageDiscVal))) / 100);
            } else {
                $packageDiscAmount = min($subtotal, $packageDiscVal);
            }

            $finalPrice = max(0, $subtotal - $packageDiscAmount);

            $package = ServicePackage::create([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'description' => $request->input('description'),
                'total_amount' => $subtotal,
                'discount_type' => $packageDiscType,
                'discount_value' => $packageDiscVal,
                'final_price' => $finalPrice,
                'status' => $request->input('status', 'active'),
            ]);

            foreach ($processedItems as $pItem) {
                $package->items()->create($pItem);
            }
        });

        return redirect()
            ->route('services.packages.index')
            ->with('success', 'پکیج با موفقیت تعریف شد.');
    }

    public function show(ServicePackage $package)
    {
        $package->load(['items.service.customFields']);
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::packages.show', compact('package', 'currency'));
    }

    public function edit(ServicePackage $package)
    {
        $package->load(['items.service.customFields']);
        $services = Service::active()->with('customFields')->orderBy('name')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::packages.edit', compact('package', 'services', 'currency'));
    }

    public function update(StoreServicePackageRequest $request, ServicePackage $package)
    {
        DB::transaction(function () use ($request, $package) {
            $itemsData = $request->input('items', []);
            $subtotal = 0;
            $processedItems = [];

            foreach ($itemsData as $item) {
                $qty = floatval($this->parsePrice($item['quantity'] ?? 1));
                if ($qty <= 0) $qty = 1;
                $unitPrice = intval($this->parsePrice($item['unit_price'] ?? 0));
                $discType = $item['discount_type'] ?? 'amount';
                $discVal = floatval($this->parsePrice($item['discount_value'] ?? 0));

                $customFields = $item['custom_fields'] ?? [];
                $rawCustomFieldsPrices = $item['custom_fields_prices'] ?? [];
                $customFieldsPrices = [];

                foreach ($rawCustomFieldsPrices as $k => $v) {
                    $customFieldsPrices[$k] = $this->parsePrice($v);
                }

                $cfSubtotal = 0;
                if (!empty($item['service_id'])) {
                    $service = Service::with('customFields')->find($item['service_id']);
                    if ($service && !empty($service->customFields)) {
                        foreach ($service->customFields as $cf) {
                            $val = $customFields[$cf->id] ?? null;
                            $isSelected = false;
                            if ($val !== null && $val !== '') {
                                if ($cf->type === 'checkbox') {
                                    $isSelected = !empty($val);
                                } elseif ($cf->type === 'multiselect') {
                                    $isSelected = is_array($val) && count($val) > 0;
                                } else {
                                    $isSelected = true;
                                }
                            }
                            if ($cf->has_pricing && $isSelected) {
                                $cfPrice = 0;
                                if (isset($customFieldsPrices[$cf->id]) && $customFieldsPrices[$cf->id] > 0) {
                                    $cfPrice = floatval($customFieldsPrices[$cf->id]);
                                } else {
                                    $cfAmount = floatval($cf->pricing_amount ?? 0);
                                    $cfPrice = ($cf->pricing_type === 'percentage') ? ($unitPrice * ($cfAmount / 100)) : $cfAmount;
                                    $customFieldsPrices[$cf->id] = $cfPrice;
                                }
                                $cfSubtotal += ($cfPrice * $qty);
                            }
                        }
                    }
                }

                $rowSubtotal = ($qty * $unitPrice) + $cfSubtotal;
                if ($discType === 'percent') {
                    $discAmount = round(($rowSubtotal * min(100, max(0, $discVal))) / 100);
                } else {
                    $discAmount = min($rowSubtotal, $discVal);
                }

                $rowTotal = max(0, $rowSubtotal - $discAmount);
                $subtotal += $rowTotal;

                $processedItems[] = [
                    'service_id' => !empty($item['service_id']) ? $item['service_id'] : null,
                    'custom_service_name' => $item['custom_service_name'] ?? '',
                    'description' => $item['description'] ?? '',
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'عدد',
                    'unit_price' => $unitPrice,
                    'discount_type' => $discType,
                    'discount_value' => $discVal,
                    'discount_amount' => $discAmount,
                    'billing_period' => $item['billing_period'] ?? null,
                    'custom_fields' => $customFields,
                    'custom_fields_prices' => $customFieldsPrices,
                    'total_price' => $rowTotal,
                ];
            }

            $packageDiscType = $request->input('discount_type', 'amount');
            $packageDiscVal = floatval($this->parsePrice($request->input('discount_value', 0)));

            if ($packageDiscType === 'percent') {
                $packageDiscAmount = round(($subtotal * min(100, max(0, $packageDiscVal))) / 100);
            } else {
                $packageDiscAmount = min($subtotal, $packageDiscVal);
            }

            $finalPrice = max(0, $subtotal - $packageDiscAmount);

            $package->update([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'description' => $request->input('description'),
                'total_amount' => $subtotal,
                'discount_type' => $packageDiscType,
                'discount_value' => $packageDiscVal,
                'final_price' => $finalPrice,
                'status' => $request->input('status', 'active'),
            ]);

            $package->items()->delete();
            foreach ($processedItems as $pItem) {
                $package->items()->create($pItem);
            }
        });

        return redirect()
            ->route('services.packages.index')
            ->with('success', 'پکیج با موفقیت به روزرسانی شد.');
    }

    public function destroy(ServicePackage $package)
    {
        $package->delete();

        return redirect()
            ->route('services.packages.index')
            ->with('success', 'پکیج حذف شد.');
    }

    public function getPackagesJson()
    {
        $packages = ServicePackage::where('status', 'active')
            ->with(['items.service.customFields'])
            ->latest()
            ->get();

        return response()->json($packages);
    }
}
