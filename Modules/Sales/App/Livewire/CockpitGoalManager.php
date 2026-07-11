<?php

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Modules\Sales\App\Models\CockpitGoal;

class CockpitGoalManager extends Component
{
    public bool $showCreateModal = false;
    public ?int $editingGoalId = null;

    public ?int $selectedUserId = null;
    public bool $isManager = false;

    #[Validate('required|in:daily_calls,daily_answered,weekly_followups,monthly_clients,conversion_rate,talk_time_minutes')]
    public string $goal_type = 'daily_calls';

    #[Validate('required|integer|min:1')]
    public int $target_value = 10;

    #[Validate('required|in:daily,weekly,monthly')]
    public string $period = 'daily';

    #[Validate('nullable|date')]
    public ?string $active_from = null;

    #[Validate('nullable|date')]
    public ?string $active_until = null;

    #[Validate('nullable|string|max:255')]
    public ?string $note = null;

    public function mount()
    {
        $this->active_from = today()->format('Y-m-d');
        $user = auth()->user();
        $this->isManager = $user->hasRole('super-admin') || $user->can('sales.manage');
        $this->selectedUserId = auth()->id();
    }

    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->reset(['editingGoalId', 'note']);
        $this->goal_type = 'daily_calls';
        $this->target_value = 10;
        $this->period = 'daily';
        $this->active_from = today()->format('Y-m-d');
        $this->active_until = null;
        $this->showCreateModal = true;
    }

    public function editGoal($id)
    {
        $this->resetErrorBag();
        $goal = CockpitGoal::findOrFail($id);
        $this->editingGoalId = $goal->id;
        $this->goal_type = $goal->goal_type;
        $this->target_value = $goal->target_value;
        $this->period = $goal->period;
        $this->active_from = $goal->active_from ? $goal->active_from->format('Y-m-d') : null;
        $this->active_until = $goal->active_until ? $goal->active_until->format('Y-m-d') : null;
        $this->note = $goal->note;
        $this->showCreateModal = true;
    }

    public function saveGoal()
    {
        $this->validate();

        $targetUser = $this->isManager ? ($this->selectedUserId ?? auth()->id()) : auth()->id();

        $data = [
            'user_id' => $targetUser,
            'goal_type' => $this->goal_type,
            'target_value' => $this->target_value,
            'period' => $this->period,
            'active_from' => $this->active_from ?: null,
            'active_until' => $this->active_until ?: null,
            'note' => $this->note,
        ];

        if ($this->editingGoalId) {
            $goal = CockpitGoal::findOrFail($this->editingGoalId);
            $goal->update($data);
            $message = 'هدف با موفقیت ویرایش شد.';
        } else {
            $data['created_by'] = auth()->id();
            $data['is_active'] = true;
            CockpitGoal::create($data);
            $message = 'هدف جدید با موفقیت اضافه شد.';
        }

        $this->showCreateModal = false;
        $this->editingGoalId = null;
        $this->dispatch('refreshStats');
        $this->dispatch('refreshToday');
        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function toggleGoalActive($id)
    {
        $goal = CockpitGoal::findOrFail($id);
        $goal->update(['is_active' => !$goal->is_active]);
        $this->dispatch('refreshStats');
        $this->dispatch('refreshToday');
        $this->dispatch('notify', message: 'وضعیت هدف تغییر یافت.', type: 'success');
    }

    public function deleteGoal($id)
    {
        $goal = CockpitGoal::findOrFail($id);
        $goal->delete();
        $this->dispatch('refreshStats');
        $this->dispatch('refreshToday');
        $this->dispatch('notify', message: 'هدف حذف شد.', type: 'success');
    }

    public function render()
    {
        $targetUser = $this->isManager ? ($this->selectedUserId ?? auth()->id()) : auth()->id();

        $goals = CockpitGoal::where('user_id', $targetUser)
            ->orderBy('is_active', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $usersList = $this->isManager ? \App\Models\User::orderBy('name')->get() : collect();

        return view('sales::livewire.goal-manager', [
            'goals' => $goals,
            'goalTypes' => CockpitGoal::goalTypeLabels(),
            'usersList' => $usersList,
        ]);
    }
}
