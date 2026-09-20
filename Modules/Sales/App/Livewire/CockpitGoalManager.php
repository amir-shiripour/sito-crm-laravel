<?php

declare(strict_types=1);

namespace Modules\Sales\App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Modules\Sales\App\Models\CockpitGoal;
use App\Models\User;
use Morilog\Jalali\Jalalian;

class CockpitGoalManager extends Component
{
    public bool $showCreateModal = false;
    public ?int $editingGoalId = null;

    public ?int $selectedUserId = null;
    public bool $isManager = false;

    // Filters
    public string $filterPeriod = 'all'; // 'all', 'daily', 'weekly', 'monthly'
    public string $filterStatus = 'all'; // 'all', 'active', 'achieved', 'inactive'

    #[Validate('required|in:daily_calls,daily_answered,weekly_followups,monthly_clients,conversion_rate,talk_time_minutes')]
    public string $goal_type = 'daily_calls';

    #[Validate('required|integer|min:1')]
    public int $target_value = 10;

    #[Validate('required|in:daily,weekly,monthly')]
    public string $period = 'daily';

    #[Validate('nullable|date')]
    public ?string $active_from = null;

    public ?string $active_from_jalali = null;

    #[Validate('nullable|date')]
    public ?string $active_until = null;

    public ?string $active_until_jalali = null;

    #[Validate('nullable|string|max:255')]
    public ?string $note = null;

    public function mount(): void
    {
        $this->active_from = today()->format('Y-m-d');
        $this->active_from_jalali = Jalalian::now()->format('Y/m/d');
        $user = auth()->user();
        $this->isManager = $user ? ($user->hasRole('super-admin') || $user->can('sales.manage')) : false;
        $this->selectedUserId = auth()->id();
    }

    #[Computed]
    public function goalStats(): array
    {
        $targetUserId = $this->isManager ? ($this->selectedUserId ?? auth()->id()) : auth()->id();
        $user = $targetUserId ? User::find($targetUserId) : null;
        if (!$user) {
            return [
                'total' => 0,
                'achieved' => 0,
                'in_progress' => 0,
                'avg_progress' => 0,
            ];
        }

        $goals = CockpitGoal::where('user_id', $targetUserId)
            ->where('is_active', true)
            ->get();

        $total = $goals->count();
        $achieved = 0;
        $sumPercent = 0;

        foreach ($goals as $goal) {
            $progress = $goal->calculateProgress($user);
            $percent = (int) ($progress['percent'] ?? 0);
            $sumPercent += $percent;
            if ($percent >= 100) {
                $achieved++;
            }
        }

        $inProgress = max(0, $total - $achieved);
        $avgProgress = $total > 0 ? (int) round($sumPercent / $total) : 0;

        return [
            'total' => $total,
            'achieved' => $achieved,
            'in_progress' => $inProgress,
            'avg_progress' => $avgProgress,
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->reset(['editingGoalId', 'note', 'active_until', 'active_until_jalali']);
        $this->goal_type = 'daily_calls';
        $this->target_value = 10;
        $this->period = 'daily';
        $this->active_from = today()->format('Y-m-d');
        $this->active_from_jalali = Jalalian::now()->format('Y/m/d');
        $this->showCreateModal = true;
    }

    public function editGoal(int $id): void
    {
        $this->resetErrorBag();
        $goal = CockpitGoal::findOrFail($id);
        $this->editingGoalId = $goal->id;
        $this->goal_type = $goal->goal_type;
        $this->target_value = $goal->target_value;
        $this->period = $goal->period;
        $this->active_from = $goal->active_from ? $goal->active_from->format('Y-m-d') : null;
        $this->active_from_jalali = $goal->active_from ? Jalalian::fromDateTime($goal->active_from)->format('Y/m/d') : null;
        $this->active_until = $goal->active_until ? $goal->active_until->format('Y-m-d') : null;
        $this->active_until_jalali = $goal->active_until ? Jalalian::fromDateTime($goal->active_until)->format('Y/m/d') : null;
        $this->note = $goal->note;
        $this->showCreateModal = true;
    }

    public function setPreset(string $type, int $value, string $period): void
    {
        $this->goal_type = $type;
        $this->target_value = $value;
        $this->period = $period;
    }

    private function parseJalaliDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) {
            return null;
        }

        // Convert Persian/Arabic digits to English digits
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $normalized = str_replace($persian, $english, $dateStr);
        $normalized = str_replace($arabic, $english, $normalized);

        // If already Gregorian Y-m-d
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized)) {
            return $normalized;
        }

        try {
            $parts = preg_split('/[\/\-]/', $normalized);
            if ($parts && count($parts) === 3) {
                $y = (int) $parts[0];
                $m = (int) $parts[1];
                $d = (int) $parts[2];
                if ($y > 1300 && $y < 1500) {
                    return (new Jalalian($y, $m, $d))->toCarbon()->toDateString();
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return null;
    }

    public function saveGoal(): void
    {
        if (!empty($this->active_from_jalali)) {
            $parsed = $this->parseJalaliDate($this->active_from_jalali);
            if ($parsed) {
                $this->active_from = $parsed;
            }
        }

        if (!empty($this->active_until_jalali)) {
            $parsed = $this->parseJalaliDate($this->active_until_jalali);
            if ($parsed) {
                $this->active_until = $parsed;
            }
        }

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
            $message = 'هدف عملکردی با موفقیت ویرایش شد.';
        } else {
            $data['created_by'] = auth()->id();
            $data['is_active'] = true;
            CockpitGoal::create($data);
            $message = 'هدف عملکردی جدید با موفقیت اضافه شد.';
        }

        $this->showCreateModal = false;
        $this->editingGoalId = null;
        $this->dispatch('refreshStats');
        $this->dispatch('refreshToday');
        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function toggleGoalActive(int $id): void
    {
        $goal = CockpitGoal::findOrFail($id);
        $goal->update(['is_active' => !$goal->is_active]);
        $this->dispatch('refreshStats');
        $this->dispatch('refreshToday');
        $this->dispatch('notify', message: 'وضعیت هدف با موفقیت تغییر یافت.', type: 'success');
    }

    public function deleteGoal(int $id): void
    {
        $goal = CockpitGoal::findOrFail($id);
        $goal->delete();
        $this->dispatch('refreshStats');
        $this->dispatch('refreshToday');
        $this->dispatch('notify', message: 'هدف عملکردی حذف شد.', type: 'success');
    }

    public function render()
    {
        $targetUserId = $this->isManager ? ($this->selectedUserId ?? auth()->id()) : auth()->id();
        $user = $targetUserId ? User::find($targetUserId) : auth()->user();

        $query = CockpitGoal::query()->where('user_id', $targetUserId);

        if ($this->filterPeriod !== 'all') {
            $query->where('period', $this->filterPeriod);
        }

        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }

        $goals = $query->orderBy('is_active', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Enrich goals with live progress calculations
        $enrichedGoals = $goals->map(function(CockpitGoal $goal) use ($user) {
            $progress = $user ? $goal->calculateProgress($user) : [
                'current' => 0,
                'target' => $goal->target_value,
                'percent' => 0,
            ];

            $percent = (int) ($progress['percent'] ?? 0);
            $isAchieved = ($percent >= 100);

            return (object) [
                'id' => $goal->id,
                'goal_type' => $goal->goal_type,
                'goal_type_label' => $goal->getGoalTypeLabel(),
                'target_value' => $goal->target_value,
                'period' => $goal->period,
                'is_active' => (bool) $goal->is_active,
                'active_from' => $goal->active_from,
                'active_until' => $goal->active_until,
                'note' => $goal->note,
                'current' => $progress['current'] ?? 0,
                'percent' => $percent,
                'is_achieved' => $isAchieved,
            ];
        });

        // Filter achieved if specifically requested
        if ($this->filterStatus === 'achieved') {
            $enrichedGoals = $enrichedGoals->filter(fn($g) => $g->is_achieved && $g->is_active)->values();
        }

        $usersList = $this->isManager ? User::orderBy('name')->get(['id', 'name']) : collect();

        return view('sales::livewire.goal-manager', [
            'goals' => $enrichedGoals,
            'goalTypes' => CockpitGoal::goalTypeLabels(),
            'usersList' => $usersList,
            'goalStats' => $this->goalStats,
        ]);
    }
}
