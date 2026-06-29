<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\ClientCalls\Entities\ClientCall;
use Modules\Tasks\Entities\Task;
use Modules\Reminders\Entities\Reminder;
use Modules\Sales\App\Models\CockpitGoal;

class TodayTab extends Component
{
    public ?int $selectedClientId = null;

    #[On('clientChanged')]
    public function updateSelectedClient($clientId)
    {
        $this->selectedClientId = $clientId;
    }

    #[On('refreshToday')]
    public function refresh()
    {
        // just trigger render
    }

    public function completeTask($id)
    {
        if (class_exists(Task::class)) {
            $task = Task::findOrFail($id);
            $task->update(['status' => Task::STATUS_DONE]);
            $this->dispatch('refreshStats');
            $this->dispatch('notify', message: 'وظیفه با موفقیت انجام شد.', type: 'success');
        }
    }

    public function dismissReminder($id)
    {
        if (class_exists(Reminder::class)) {
            $reminder = Reminder::findOrFail($id);
            $reminder->update(['status' => 'DONE']);
            $this->dispatch('refreshStats');
            $this->dispatch('notify', message: 'یادآوری انجام شد.', type: 'success');
        }
    }

    public function initiateTodayCall($clientId)
    {
        $this->dispatch('clientSelected', clientId: $clientId);
        $this->dispatch('startCallFromToday');
    }

    public function render()
    {
        $userId = auth()->id();

        // 1. Today's planned calls
        $calls = collect();
        if (class_exists(ClientCall::class)) {
            $calls = ClientCall::where('user_id', $userId)
                ->whereDate('call_date', today())
                ->where('status', 'planned')
                ->with('client')
                ->get();
        }

        // 2. Today's due tasks
        $tasks = collect();
        if (class_exists(Task::class)) {
            $tasks = Task::where('assignee_id', $userId)
                ->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS])
                ->whereDate('due_at', today())
                ->with('relatedClient')
                ->get();
        }

        // 3. Today's active reminders
        $reminders = collect();
        if (class_exists(Reminder::class)) {
            $reminders = Reminder::where('user_id', $userId)
                ->where('status', 'OPEN')
                ->whereDate('remind_at', today())
                ->get();
        }

        // 4. Goals progress list
        $goalsProgress = [];
        $activeGoals = CockpitGoal::where('user_id', $userId)
            ->active()
            ->get();

        foreach ($activeGoals as $goal) {
            $goalsProgress[] = $goal->calculateProgress(auth()->user());
        }

        return view('sales::livewire.today-tab', [
            'todayCalls' => $calls,
            'todayTasks' => $tasks,
            'todayReminders' => $reminders,
            'goalsProgress' => $goalsProgress,
        ]);
    }
}
