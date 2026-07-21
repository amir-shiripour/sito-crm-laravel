<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Services\App\Http\Models\Service;
use Modules\Services\App\Http\Models\ServiceCategory;
use Modules\Services\App\Http\Models\ServiceTemplate;
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
            ->withCount('projects')
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
            'templates' => ServiceTemplate::orderBy('name')->get(),
            'statuses' => Status::where('type', 'service')->orderBy('sort_order')->get(),
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
            'template',
            'customFields' => fn($q) => $q->orderBy('sort_order'),
        ]);

        $service->loadCount('projects');

        $revenue = $service->invoices()
            ->whereHas('status', fn($s) => $s->where('name', 'paid'))->sum('total') ?? 0;

        $recentProjects = $service->projects()->with('status')->latest()->limit(5)->get();
        $recentInvoices = $service->invoices()->with('status')->latest()->limit(5)->get();
        $currency = Setting::where('key', 'currency')->value('value') ?? 'toman';

        return view('services::services.show', compact(
            'service',
            'revenue',
            'recentProjects',
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
            'templates' => ServiceTemplate::orderBy('name')->get(),
            'statuses' => Status::where('type', 'service')->orderBy('sort_order')->get(),
            'currency' => $currency,
        ]);
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
            $field['options'] = isset($field['options_text'])
                ? array_filter(array_map('trim', explode("\n", $field['options_text'])))
                : [];
            unset($field['options_text']);

            $field['is_required'] = $field['is_required'] ?? false;
            $field['show_in_invoice'] = $field['show_in_invoice'] ?? false;
            $field['has_pricing'] = $field['has_pricing'] ?? false;

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
