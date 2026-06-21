<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Cache;
use Modules\ClientCalls\Entities\ClientCall;
use Modules\Tasks\Entities\Task;
use Modules\Reminders\Entities\Reminder;
use Modules\Sales\App\Models\CockpitGoal;

class CockpitMain extends Component
{
    public string $activeTab = 'customers'; // 'customers', 'calls', 'tasks', 'today'
    public ?int $selectedClientId = null;
    public bool $showNewClientModal = false;
    public bool $showNewCallModal = false;
    public bool $showNewFollowupModal = false;
    
    // Stats for header
    public $stats = [
        'total_clients'      => 0,
        'calls_today'        => 0,
        'answered_today'     => 0,
        'pending_tasks'      => 0,
        'overdue_tasks'      => 0,
        'my_calls_today'     => 0,
        'my_answered_today'  => 0,
    ];

    public string $globalSearch = '';
    public ?string $quickNote = null;
    public bool $showSmsPanel = false;
    public string $smsText = '';

    protected $queryString = ['activeTab', 'selectedClientId'];

    public function mount()
    {
        if (!in_array($this->activeTab, ['customers', 'calls', 'tasks', 'today'])) {
            $this->activeTab = 'customers';
        }
        $this->loadStats();
    }

    public function loadStats()
    {
        $userId = auth()->id();
        $cacheKey = "cockpit_stats_user_{$userId}";

        $this->stats = Cache::remember($cacheKey, 60, function () use ($userId) {
            $user = auth()->user();

            $totalClients = 0;
            if (class_exists(\Modules\Clients\Entities\Client::class)) {
                $totalClients = \Modules\Clients\Entities\Client::visibleForUser($user)->count();
            }

            $callsToday = 0;
            $answeredToday = 0;
            $myCallsToday = 0;
            $myAnsweredToday = 0;
            if (class_exists(ClientCall::class)) {
                $callsToday = ClientCall::today()->count();
                $answeredToday = ClientCall::today()->answered()->count();
                $myCallsToday = ClientCall::where('user_id', $userId)->today()->count();
                $myAnsweredToday = ClientCall::where('user_id', $userId)->today()->answered()->count();
            }

            $pendingTasks = 0;
            $overdueTasks = 0;
            if (class_exists(Task::class)) {
                $pendingTasks = Task::where('assignee_id', $userId)
                    ->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS])
                    ->count();

                $overdueTasks = Task::where('assignee_id', $userId)
                    ->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS])
                    ->where('due_at', '<', now())
                    ->count();
            }

            return [
                'total_clients' => $totalClients,
                'calls_today' => $callsToday,
                'answered_today' => $answeredToday,
                'pending_tasks' => $pendingTasks,
                'overdue_tasks' => $overdueTasks,
                'my_calls_today' => $myCallsToday,
                'my_answered_today' => $myAnsweredToday,
            ];
        });
    }

    #[On('refreshStats')]
    public function refreshStats()
    {
        $userId = auth()->id();
        Cache::forget("cockpit_stats_user_{$userId}");
        $this->loadStats();
    }

    #[On('clientSelected')]
    public function selectClient($clientId)
    {
        $this->selectedClientId = $clientId;
        
        $this->quickNote = null;
        if ($clientId && class_exists(\Modules\Clients\Entities\Client::class)) {
            $client = \Modules\Clients\Entities\Client::find($clientId);
            if ($client) {
                $this->quickNote = $client->notes;
            }
        }
        
        $this->dispatch('clientChanged', clientId: $clientId);
    }

    public function clearSelection()
    {
        $this->selectedClientId = null;
        $this->quickNote = null;
        $this->dispatch('clientChanged', clientId: null);
    }

    #[On('transferTab')]
    public function switchTab($tab)
    {
        if (in_array($tab, ['customers', 'calls', 'tasks', 'today'])) {
            $this->activeTab = $tab;
        }
    }

    #[On('startCallFromToday')]
    public function startCallFromToday()
    {
        $this->initiateCall();
    }

    public function saveQuickNote()
    {
        if (!$this->selectedClientId) return;

        if (class_exists(\Modules\Clients\Entities\Client::class)) {
            $client = \Modules\Clients\Entities\Client::find($this->selectedClientId);
            if ($client) {
                $client->update(['notes' => $this->quickNote]);
                $this->dispatch('notify', message: 'یادداشت با موفقیت ذخیره شد.', type: 'success');
            }
        }
    }

    public function sendQuickSms()
    {
        $this->validate(['smsText' => 'required|string|min:3']);

        $clientClass = '\Modules\Clients\Entities\Client';
        $smsManagerClass = '\Modules\Sms\Services\SmsManager';
        $smsMessageClass = '\Modules\Sms\Entities\SmsMessage';

        if (class_exists($clientClass) && class_exists($smsManagerClass)) {
            $client = $clientClass::find($this->selectedClientId);
            if ($client && $client->phone) {
                $type = class_exists($smsMessageClass) ? $smsMessageClass::TYPE_MANUAL : 'MANUAL';

                app($smsManagerClass)->sendText(
                    $client->phone,
                    $this->smsText,
                    ['type' => $type, 'meta' => ['source' => 'cockpit']]
                );

                $this->smsText = '';
                $this->showSmsPanel = false;
                $this->dispatch('notify', message: 'پیامک با موفقیت ارسال شد.', type: 'success');
            }
        } else {
            $this->dispatch('notify', message: 'سرویس پیامک در دسترس نیست.', type: 'error');
        }
    }

    public function initiateCall()
    {
        if (!$this->selectedClientId) return;

        $clientClass = '\Modules\Clients\Entities\Client';
        if (class_exists($clientClass)) {
            $client = $clientClass::find($this->selectedClientId);
            if ($client) {
                if (class_exists(ClientCall::class)) {
                    ClientCall::create([
                        'client_id' => $this->selectedClientId,
                        'user_id'   => auth()->id(),
                        'call_date' => today()->format('Y-m-d'),
                        'call_time' => now()->format('H:i'),
                        'direction' => 'outbound',
                        'status'    => 'planned',
                        'contact_phone' => $client->phone,
                        'reason'    => 'تماس خروجی سیستم',
                    ]);
                }

                $this->dispatch('voip-initiate', phone: $client->phone, clientId: $client->id);
                $this->activeTab = 'calls';
                $this->refreshStats();
            }
        }
    }

    public function getGoalProgressProperty(): array
    {
        $goal = CockpitGoal::getActiveGoalForUser(auth()->user());
        if ($goal) {
            return $goal->calculateProgress(auth()->user());
        }
        return ['has_goal' => false];
    }

    public function updatedGlobalSearch()
    {
        $this->dispatch('globalSearchTriggered', search: $this->globalSearch);
    }

    public function render()
    {
        $selectedClient = null;
        $lastCalls = collect();
        $pendingFollowups = collect();
        $activeReminders = collect();

        if ($this->selectedClientId && class_exists(\Modules\Clients\Entities\Client::class)) {
            $selectedClient = \Modules\Clients\Entities\Client::find($this->selectedClientId);
            
            if ($selectedClient) {
                if (class_exists(ClientCall::class)) {
                    $lastCalls = ClientCall::where('client_id', $this->selectedClientId)
                        ->latest('call_date')
                        ->latest('call_time')
                        ->take(5)
                        ->get();
                }

                if (class_exists(Task::class)) {
                    $pendingFollowups = Task::where('related_type', Task::RELATED_TYPE_CLIENT)
                        ->where('related_id', $this->selectedClientId)
                        ->where('task_type', Task::TYPE_FOLLOW_UP)
                        ->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS])
                        ->latest('due_at')
                        ->take(5)
                        ->get();
                }

                if (class_exists(Reminder::class)) {
                    $activeReminders = Reminder::where('user_id', auth()->id())
                        ->where('status', 'OPEN')
                        ->where(function($q) {
                            $q->where(function($sub) {
                                $sub->where('related_type', 'CLIENT')
                                    ->where('related_id', $this->selectedClientId);
                            })->orWhere(function($sub) {
                                $sub->where('related_type', 'TASK')
                                    ->whereIn('related_id', function($query) {
                                        $query->select('id')
                                            ->from('tasks')
                                            ->where('related_type', 'CLIENT')
                                            ->where('related_id', $this->selectedClientId);
                                    });
                            });
                        })
                        ->orderBy('remind_at')
                        ->take(5)
                        ->get();
                }
            }
        }

        return view('sales::livewire.cockpit-main', [
            'selectedClient' => $selectedClient,
            'lastCalls' => $lastCalls,
            'pendingFollowups' => $pendingFollowups,
            'activeReminders' => $activeReminders,
            'goalProgress' => $this->goalProgress,
        ]);
    }
}
