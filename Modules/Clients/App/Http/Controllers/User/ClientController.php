<?php

namespace Modules\Clients\App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\Entities\Cheque;
use Modules\Clients\Entities\Client;
use Modules\Clients\Entities\ClientForm;
use Modules\Clients\Entities\ClientSetting;
use Modules\Clients\Entities\ClientStatus;
use Modules\Clients\App\Http\Requests\StoreClientRequest;
use Modules\Clients\App\Http\Requests\UpdateClientRequest;
use Modules\Services\App\Http\Models\Invoice;
use Modules\Services\App\Http\Models\Order;
use Modules\Services\App\Http\Models\Payment;
use Modules\Workflows\Entities\Workflow;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:clients.view')->only(['index', 'show']);
        $this->middleware('permission:clients.create')->only(['create', 'store']);
        $this->middleware('permission:clients.edit')->only(['edit', 'update']);
        $this->middleware('permission:clients.delete')->only(['destroy', 'restore', 'forceDelete']);
    }


    public function index(Request $request)
    {
        session(['clients_index_url' => $request->fullUrl()]);

        $user = auth()->user();
        $isTrash = $request->has('trashed') && $request->trashed == '1';

        $query = Client::visibleForUser($user)
            ->with(['creator', 'status', 'calls.user']);

        if ($isTrash) {
            $query->onlyTrashed();
        }

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('full_name', 'like', "%{$searchTerm}%")
                    ->orWhere('username', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('phone', 'like', "%{$searchTerm}%")
                    ->orWhere('case_number', 'like', "%{$searchTerm}%")
                    ->orWhere('national_code', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->input('created_by'));
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->input('status_id'));
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('id', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('full_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('full_name', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $clients = $query->paginate(12)
            ->appends($request->query());

        $users = User::all();
        $statuses = ClientStatus::all();
        $isBookingQueueEnabled = $this->isBookingQueueEnabled();

        return view('clients::user.clients.index', compact('clients', 'users', 'statuses', 'isBookingQueueEnabled'));
    }

    public function create()
    {
        return view('clients::user.clients.create');
    }


    public function store(StoreClientRequest $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email',
            'phone' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();

        Client::create($data);

        return redirect()
            ->to(session('clients_index_url', route('user.clients.index')))
            ->with('success', 'Client created.');
    }


    protected function ensureVisible(Client $client): void
    {
        $user = auth()->user();

        if (!$client->isVisibleFor($user)) {
            abort(403, 'شما به این پرونده دسترسی ندارید.');
        }
    }

    public function show(Client $client)
    {
        $this->ensureVisible($client);

        $relations = ['creator', 'status'];

        $clientCallsModule = Module::where('slug', 'clientcalls')->first();
        if ($clientCallsModule && $clientCallsModule->installed && $clientCallsModule->active && \Schema::hasTable('client_calls')) {
            $relations[] = 'calls.user';
        }

        $followUpsModule = Module::where('slug', 'followups')->first();
        if ($followUpsModule && $followUpsModule->installed && $followUpsModule->active && \Schema::hasTable('tasks')) {
            $relations[] = 'followUps.assignee';
        }

        $client->load($relations);
        $keyFromSettings = ClientSetting::getValue('default_form_key');
        $activeForm = ClientForm::active($keyFromSettings);

        $clientOrders = collect([]);
        $clientInvoices = collect([]);

        if (class_exists(Order::class)) {
            try {
                $clientOrders = Order::with(['customer', 'status', 'service', 'invoice.payments'])
                    ->where('customer_id', $client->id)
                    ->orderByDesc('invoice_id')
                    ->orderBy('id', 'asc')
                    ->limit(50)
                    ->get();
            } catch (\Exception $e) {
                $clientOrders = collect([]);
            }
        }

        if (class_exists(Invoice::class)) {
            try {
                $clientInvoices = Invoice::with(['customer', 'status', 'service', 'payments'])
                    ->where('customer_id', $client->id)
                    ->whereNotNull('invoice_number')
                    ->latest()
                    ->limit(50)
                    ->get();
            } catch (\Exception $e) {
                $clientInvoices = collect([]);
            }
        }

        // ── Workflows & Booking Module check ──────────────────────────────
        $bookingModule = Module::where('slug', 'booking')->first();
        $workflowsModule = Module::where('slug', 'workflows')->first();

        $isBookingActive = $bookingModule && $bookingModule->installed && $bookingModule->active;
        $isWorkflowsActive = $workflowsModule && $workflowsModule->installed && $workflowsModule->active;

        $availableWorkflows = collect([]);
        if ($isWorkflowsActive && class_exists(Workflow::class) && \Schema::hasTable('workflows')) {
            try {
                $availableWorkflows = Workflow::where('is_active', true)
                    ->whereHas('nodes')
                    ->where('key', 'not like', 'system_%')
                    ->where('key', 'not like', 'auto_%')
                    ->orderBy('name')
                    ->get();
            } catch (\Exception $e) {
                $availableWorkflows = collect([]);
            }
        }

        // ── Accounting Module check ───────────────────────────────────
        $accountingModule = Module::where('slug', 'accounting')->first();
        $isAccountingActive = $accountingModule && $accountingModule->installed && $accountingModule->active;
        $accountingDocuments = collect([]);

        if ($isAccountingActive && class_exists(Document::class)) {
            try {
                $docIds = collect();

                // 1. Documents where documentable is Accounting\Invoice for this client
                if (class_exists(\Modules\Accounting\Entities\Invoice::class)) {
                    $accInvoicesIds = \Modules\Accounting\Entities\Invoice::where('client_id', $client->id)->pluck('id')->toArray();
                    if (!empty($accInvoicesIds)) {
                        $ids = Document::where('documentable_type', \Modules\Accounting\Entities\Invoice::class)
                            ->whereIn('documentable_id', $accInvoicesIds)
                            ->pluck('id')->toArray();
                        $docIds = $docIds->merge($ids);
                    }
                }

                // 2. Documents where documentable is Cheque for this client
                if (class_exists(Cheque::class) && \Schema::hasColumn('accounting_cheques', 'client_id')) {
                    $chequeIds = Cheque::where('client_id', $client->id)->pluck('id')->toArray();
                    if (!empty($chequeIds)) {
                        $ids = Document::where('documentable_type', Cheque::class)
                            ->whereIn('documentable_id', $chequeIds)
                            ->pluck('id')->toArray();
                        $docIds = $docIds->merge($ids);
                    }
                }

                // 3. Documents through SourceDocument -> Payment -> Services\Invoice
                if (class_exists(Payment::class) && class_exists(Invoice::class)) {
                    $paymentIds = Payment::whereHas('invoice', function($q) use ($client) {
                        $q->where('customer_id', $client->id);
                    })->pluck('id')->toArray();

                    if (!empty($paymentIds)) {
                        $ids = \Modules\Accounting\App\Models\SourceDocument::where('sourceable_type', Payment::class)
                            ->whereIn('sourceable_id', $paymentIds)
                            ->pluck('document_id')->toArray();
                        $docIds = $docIds->merge($ids);
                    }
                }

                // 4. Documents through SourceDocument -> Services\Invoice
                if (class_exists(Invoice::class)) {
                    $srvInvoicesIds = Invoice::where('customer_id', $client->id)->pluck('id')->toArray();

                    if (!empty($srvInvoicesIds)) {
                        $ids = \Modules\Accounting\App\Models\SourceDocument::where('sourceable_type', Invoice::class)
                            ->whereIn('sourceable_id', $srvInvoicesIds)
                            ->pluck('document_id')->toArray();
                        $docIds = $docIds->merge($ids);
                    }
                }

                // 5. Documents through SourceDocument -> Services\Order
                if (class_exists(Order::class)) {
                    $srvOrderIds = Order::where('customer_id', $client->id)->pluck('id')->toArray();

                    if (!empty($srvOrderIds)) {
                        $ids = \Modules\Accounting\App\Models\SourceDocument::where('sourceable_type', Order::class)
                            ->whereIn('sourceable_id', $srvOrderIds)
                            ->pluck('document_id')->toArray();
                        $docIds = $docIds->merge($ids);
                    }
                }

                $uniqueDocIds = $docIds->unique()->toArray();

                if (!empty($uniqueDocIds)) {
                    $accountingDocuments = Document::with(['transactions', 'sourceDocument'])
                        ->whereIn('id', $uniqueDocIds)
                        ->latest('document_date')
                        ->latest('id')
                        ->get();
                }
            } catch (\Exception $e) {
                // Keep it empty on error
            }
        }

        // ── Booking Waitlists check ──────────────────────────────────
        $isBookingQueueEnabled = $this->isBookingQueueEnabled();
        $clientWaitlists = collect([]);
        if ($isBookingQueueEnabled && class_exists(\Modules\Booking\Entities\BookingWaitlist::class)) {
            try {
                $clientWaitlists = \Modules\Booking\Entities\BookingWaitlist::with(['service', 'provider'])
                    ->where('client_id', $client->id)
                    ->whereIn('status', [
                        \Modules\Booking\Entities\BookingWaitlist::STATUS_WAITING,
                        \Modules\Booking\Entities\BookingWaitlist::STATUS_NOTIFIED,
                        \Modules\Booking\Entities\BookingWaitlist::STATUS_IN_PROGRESS
                    ])
                    ->get()
                    ->sortBy(fn($item) => $item->queue_rank ?? $item->position ?? 0)
                    ->values();
            } catch (\Exception $e) {
                $clientWaitlists = collect([]);
            }
        }

        // ── Booking Appointments check ──────────────────────────────
        $clientAppointments = collect([]);
        if ($isBookingActive && class_exists(\Modules\Booking\Entities\Appointment::class)) {
            try {
                $clientAppointments = \Modules\Booking\Entities\Appointment::with(['service', 'provider', 'payments'])
                    ->where('client_id', $client->id)
                    ->orderByDesc('start_at_utc')
                    ->get();
            } catch (\Exception $e) {
                $clientAppointments = collect([]);
            }
        }

        return view('clients::user.clients.show', compact(
            'client', 'activeForm', 'clientOrders', 'clientInvoices',
            'bookingModule', 'workflowsModule', 'availableWorkflows',
            'accountingModule', 'accountingDocuments',
            'isBookingQueueEnabled', 'clientWaitlists',
            'clientAppointments'
        ));
    }

    public function edit(Client $client)
    {
        $this->ensureVisible($client);

        return view('clients::user.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->ensureVisible($client);

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => "nullable|email|unique:clients,email,{$client->id}",
            'phone' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $client->update($data);

        return redirect()
            ->to(session('clients_index_url', route('user.clients.index')))
            ->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $this->ensureVisible($client);

        $client->delete();

        return back()->with('success', 'مشتری با موفقیت به سطل زباله منتقل شد.');
    }

    public function restore($id)
    {
        $client = Client::onlyTrashed()->findOrFail($id);
        $this->ensureVisible($client);

        $client->restore();

        return redirect()->route('user.clients.index', ['trashed' => 1])->with('success', 'مشتری با موفقیت بازیابی شد.');
    }

    public function forceDelete($id)
    {
        $client = Client::onlyTrashed()->findOrFail($id);
        $this->ensureVisible($client);

        $client->forceDelete();

        return redirect()->route('user.clients.index', ['trashed' => 1])->with('success', 'مشتری برای همیشه حذف شد.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:clients,id'],
            'action' => ['required', 'string', 'in:status,delete'],
            'status_id' => ['required_if:action,status', 'nullable', 'integer', 'exists:client_statuses,id'],
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');
        $user = auth()->user();
        $clientsQuery = Client::visibleForUser($user)->whereIn('id', $ids);
        $clients = $clientsQuery->get();
        $count = 0;

        if ($action === 'status') {
            $statusId = $request->input('status_id');
            foreach ($clients as $client) {
                if ($user->can('clients.edit')) {
                    $client->update(['status_id' => $statusId]);
                    $count++;
                }
            }
            return redirect()->to(session('clients_index_url', route('user.clients.index')))->with('success', "وضعیت {$count} مشتری با موفقیت تغییر کرد.");
        } elseif ($action === 'delete') {
            if (!$user->can('clients.delete')) {
                abort(403);
            }
            foreach ($clients as $client) {
                $client->delete();
                $count++;
            }
            return redirect()->to(session('clients_index_url', route('user.clients.index')))->with('success', "تعداد {$count} مشتری با موفقیت به سطل زباله منتقل شد.");
        }

        return redirect()->to(session('clients_index_url', route('user.clients.index')));
    }

    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email',
            'phone' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();

        $client = Client::create($data);
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'مشتری با موفقیت ایجاد شد.',
                'client' => $client->only(['id', 'full_name', 'email', 'phone']),
            ], 201);
        }

        return redirect()
            ->to(session('clients_index_url', route('user.clients.index')))
            ->with('success', 'Client created.');
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $ids = $request->get('ids', '');
        $limit = min((int)$request->get('limit', 20), 50); // حداکثر 50 نتیجه

        $user = auth()->user();

        $clientsQuery = Client::query()->visibleForUser($user);

        if ($ids) {
            $idsArray = array_filter(array_map('intval', explode(',', $ids)));
            if (!empty($idsArray)) {
                $clientsQuery->whereIn('id', $idsArray);
            }
        } elseif ($query) {
            $clientsQuery->where(function ($subQuery) use ($query) {
                $subQuery->where('full_name', 'like', "%{$query}%")
                    ->orWhere('national_code', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('case_number', 'like', "%{$query}%");
            });
        } else {
            return response()->json([
                'results' => [],
                'total' => 0,
            ]);
        }

        $clients = $clientsQuery
            ->select('id', 'full_name', 'national_code', 'phone', 'case_number')
            ->orderBy('full_name')
            ->limit($limit)
            ->get()
            ->map(function ($client) {
                $labelParts = [$client->full_name];
                if ($client->national_code) {
                    $labelParts[] = "کد ملی: {$client->national_code}";
                }
                if ($client->phone) {
                    $labelParts[] = "تلفن: {$client->phone}";
                }
                if ($client->case_number) {
                    $labelParts[] = "پرونده: {$client->case_number}";
                }

                return [
                    'id' => $client->id,
                    'value' => (string)$client->id,
                    'label' => implode(' | ', $labelParts),
                    'full_name' => $client->full_name,
                    'national_code' => $client->national_code,
                    'phone' => $client->phone,
                    'case_number' => $client->case_number,
                ];
            });

        return response()->json([
            'results' => $clients,
            'total' => $clients->count(),
        ]);
    }

    protected function isBookingQueueEnabled(): bool
    {
        $bookingModule = Module::where('slug', 'booking')->first();
        if (!$bookingModule || !$bookingModule->installed || !$bookingModule->active) {
            return false;
        }

        if (!class_exists(\Modules\Booking\Entities\BookingWaitlist::class) || !class_exists(\Modules\Booking\Entities\BookingSetting::class) || !\Modules\Booking\Entities\BookingSetting::isQueueEnabled()) {
            return false;
        }

        $keyFromSettings = ClientSetting::getValue('default_form_key');
        $activeForm = ClientForm::active($keyFromSettings);
        if (!$activeForm || !isset($activeForm->schema['fields']) || !is_array($activeForm->schema['fields'])) {
            return false;
        }

        foreach ($activeForm->schema['fields'] as $f) {
            if (($f['id'] ?? '') === 'booking_waitlist') {
                return true;
            }
        }

        return false;
    }
}
