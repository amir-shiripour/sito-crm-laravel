<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Services\App\Http\Models\Service;
use Modules\Services\App\Http\Models\ServiceCategory;
use Modules\Services\App\Http\Models\Status;
use Modules\Services\App\Http\Requests\StoreServiceRequest;
use Modules\Services\App\Http\Requests\UpdateServiceRequest;
use Modules\Services\App\Services\ServiceManagementService;
use Modules\Settings\Entities\Setting;

class ServicesController extends Controller
{
    public function __construct(private ServiceManagementService $svc)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Service::class);

        $services = Service::with('category')
            ->withSum(['invoices as revenue' => fn($q) => $q->whereHas('status', fn($s) => $s->where('name', 'paid'))], 'total')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%"))
            ->when($request->category_id, fn($q, $v) => $q->where('category_id', $v))
            ->when($request->status_id, fn($q, $v) => $q->where('status_id', $v))
            ->when($request->billing_type, fn($q, $v) => $q->where('billing_type', $v))
            ->orderBy('sort_order')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $categories = ServiceCategory::active()->orderBy('name')->get();
        $statuses = Status::where('type', 'service')->orderBy('sort_order')->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::services.index', compact('services', 'categories', 'statuses', 'currency'));
    }

    public function create()
    {
        $this->authorize('create', Service::class);
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::services.create', [
            'categories' => ServiceCategory::active()->orderBy('name')->get(),
            'statuses' => Status::where('type', 'service')->orderBy('sort_order')->get(),
            'accountingCategories' => $this->getAccountingCategories(),
            'currency' => $currency,
        ]);
    }

    public function store(StoreServiceRequest $request)
    {
        $data = $request->except('custom_fields');

        $service = $this->svc->create(
            $data,
            $this->mapCustomFields($request->input('custom_fields', []))
        );

        return redirect()
            ->route('services.services.index')
            ->with('success', 'سرویس با موفقیت ایجاد شد.');
    }

    public function show(Service $service)
    {
        $this->authorize('view', $service);

        $service->load([
            'category',
            'accountingCategory.fundAccounts',
            'customFields' => fn($q) => $q->orderBy('sort_order'),
        ]);

        $revenue = $service->invoices()
            ->whereHas('status', fn($s) => $s->where('name', 'paid'))->sum('total') ?? 0;

        $recentInvoices = $service->invoices()->with('status')->latest()->limit(5)->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::services.show', compact(
            'service',
            'revenue',
            'recentInvoices',
            'currency'
        ));
    }

    public function edit(Service $service)
    {
        $this->authorize('update', $service);
        $service->load('customFields');
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::services.create', [
            'service' => $service,
            'categories' => ServiceCategory::active()->orderBy('name')->get(),
            'statuses' => Status::where('type', 'service')->orderBy('sort_order')->get(),
            'accountingCategories' => $this->getAccountingCategories(),
            'currency' => $currency,
        ]);
    }

    protected function getAccountingCategories()
    {
        if (\Nwidart\Modules\Facades\Module::has('Accounting') && \Nwidart\Modules\Facades\Module::isEnabled('Accounting') && class_exists(\Modules\Accounting\App\Models\Category::class)) {
            return \Modules\Accounting\App\Models\Category::with('fundAccounts')
                ->where('status', true)
                ->orderBy('account_code')
                ->orderBy('title')
                ->get();
        }

        return collect();
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $data = $request->except('custom_fields');

        $oldStatusId = $service->status_id;

        $this->svc->update(
            $service,
            $data,
            $this->mapCustomFields($request->input('custom_fields', []))
        );

        $service->refresh();

        if ($oldStatusId !== $service->status_id) {
            $inactiveStatus = \Modules\Services\App\Http\Models\Status::where('type', 'service')
                ->where('name', 'غیر فعال')
                ->first();

            if ($inactiveStatus && $service->status_id == $inactiveStatus->id) {
                if (class_exists(\Modules\Workflows\Services\WorkflowEngine::class)) {
                    try {
                        app(\Modules\Workflows\Services\WorkflowEngine::class)->start('service_inactive', 'SERVICE', $service->id, []);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('[Workflows] Error starting service_inactive: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()
            ->route('services.services.index')
            ->with('success', 'سرویس "' . $service->name . '" با موفقیت ویرایش شد.');
    }

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);

        $serviceName = $service->name;
        $service->delete();

        return redirect()
            ->route('services.services.index')
            ->with('success', 'سرویس "' . $serviceName . '" حذف شد.');
    }

    /**
     * Map raw custom fields array from request to the format expected by the service layer.
     */
    protected function mapCustomFields(array $rawFields): array
    {
        return collect($rawFields)->map(function ($field) {
            $options = [];

            if (!empty($field['options']) && is_array($field['options'])) {
                foreach ($field['options'] as $opt) {
                    if (is_array($opt)) {
                        $label = trim($opt['label'] ?? ($opt['title'] ?? ''));
                        if ($label !== '') {
                            $priceRaw = (string)($opt['price'] ?? 0);
                            $priceNum = (float) preg_replace('/[^\d.]/', '', $priceRaw);
                            $options[] = [
                                'label' => $label,
                                'price' => $priceNum,
                                'pricing_type' => $opt['pricing_type'] ?? ($field['pricing_type'] ?? 'fixed'),
                            ];
                        }
                    } elseif (is_string($opt) && trim($opt) !== '') {
                        $options[] = [
                            'label' => trim($opt),
                            'price' => 0,
                            'pricing_type' => $field['pricing_type'] ?? 'fixed',
                        ];
                    }
                }
            } elseif (isset($field['options_text']) && trim($field['options_text']) !== '') {
                $lines = array_filter(array_map('trim', explode("\n", $field['options_text'])));
                foreach ($lines as $line) {
                    $options[] = [
                        'label' => $line,
                        'price' => 0,
                        'pricing_type' => $field['pricing_type'] ?? 'fixed',
                    ];
                }
            }

            $field['options'] = $options;
            unset($field['options_text']);

            $field['is_required'] = !empty($field['is_required']);
            $field['show_in_invoice'] = !empty($field['show_in_invoice']);
            $field['has_pricing'] = !empty($field['has_pricing']);

            return $field;
        })->toArray();
    }

    public function customFieldsIndex()
    {
        $this->authorize('viewAny', Service::class);

        $services = Service::with('customFields')
            ->orderBy('name')
            ->get();

        return view('services::custom-fields.index', compact('services'));
    }

    public function getCustomFieldsJson(Service $service)
    {
        $service->load('customFields');
        return response()->json($service->customFields);
    }
}
