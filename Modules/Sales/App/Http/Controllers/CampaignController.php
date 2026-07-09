<?php

namespace Modules\Sales\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\App\Models\Campaign;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with(['creator', 'assignee'])->orderBy('id', 'desc')->get();
        return view('sales::campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $users = \App\Models\User::all();
        $roles = class_exists(\Spatie\Permission\Models\Role::class) ? \Spatie\Permission\Models\Role::all() : collect();
        return view('sales::campaigns.create', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:sms,email,call,social',
            'status' => 'nullable|string|in:draft,active,completed,cancelled,paused',
            'goal' => 'nullable|string|max:255',
            'target_audience' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric',
            'actual_cost' => 'nullable|numeric',
            'start_date' => 'nullable|string',
            'end_date' => 'nullable|string',
            'description' => 'nullable|string',
            'assignee_value' => 'nullable|string',
        ]);

        $assigned_to = null;
        $assigned_role = null;
        if (!empty($validated['assignee_value'])) {
            if (str_contains($validated['assignee_value'], ':')) {
                [$type, $value] = explode(':', $validated['assignee_value'], 2);
                if ($type === 'user') {
                    $assigned_to = $value;
                } elseif ($type === 'role') {
                    $assigned_role = $value;
                }
            }
        }
        $validated['assigned_to'] = $assigned_to;
        $validated['assigned_role'] = $assigned_role;
        unset($validated['assignee_value']);

        $validated['start_date'] = $this->convertJalaliToGregorian($request->input('start_date'));
        $validated['end_date'] = $this->convertJalaliToGregorian($request->input('end_date'));
        $validated['created_by'] = auth()->id();

        if (isset($validated['target_audience'])) {
            $validated['target_audience'] = [$validated['target_audience']];
        }

        Campaign::create($validated);

        return redirect()->route('user.sales.campaigns.index')->with('success', 'کمپین با موفقیت ایجاد شد.');
    }

    public function show(Campaign $campaign)
    {
        return view('sales::campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign)
    {
        $users = \App\Models\User::all();
        $roles = class_exists(\Spatie\Permission\Models\Role::class) ? \Spatie\Permission\Models\Role::all() : collect();
        
        $startDateJalali = $campaign->start_date 
            ? \Morilog\Jalali\Jalalian::fromCarbon($campaign->start_date)->format('Y/m/d') 
            : null;
            
        $endDateJalali = $campaign->end_date 
            ? \Morilog\Jalali\Jalalian::fromCarbon($campaign->end_date)->format('Y/m/d') 
            : null;

        return view('sales::campaigns.edit', compact('campaign', 'users', 'roles', 'startDateJalali', 'endDateJalali'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:sms,email,call,social',
            'status' => 'nullable|string|in:draft,active,completed,cancelled,paused',
            'goal' => 'nullable|string|max:255',
            'target_audience' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric',
            'actual_cost' => 'nullable|numeric',
            'start_date' => 'nullable|string',
            'end_date' => 'nullable|string',
            'description' => 'nullable|string',
            'assignee_value' => 'nullable|string',
        ]);

        $assigned_to = null;
        $assigned_role = null;
        if (!empty($validated['assignee_value'])) {
            if (str_contains($validated['assignee_value'], ':')) {
                [$type, $value] = explode(':', $validated['assignee_value'], 2);
                if ($type === 'user') {
                    $assigned_to = $value;
                } elseif ($type === 'role') {
                    $assigned_role = $value;
                }
            }
        }
        $validated['assigned_to'] = $assigned_to;
        $validated['assigned_role'] = $assigned_role;
        unset($validated['assignee_value']);

        $validated['start_date'] = $this->convertJalaliToGregorian($request->input('start_date'));
        $validated['end_date'] = $this->convertJalaliToGregorian($request->input('end_date'));

        if (isset($validated['target_audience'])) {
            $validated['target_audience'] = [$validated['target_audience']];
        }

        $campaign->update($validated);

        return redirect()->route('user.sales.campaigns.index')->with('success', 'کمپین با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('user.sales.campaigns.index')->with('success', 'کمپین با موفقیت حذف شد.');
    }

    protected function convertJalaliToGregorian(?string $jalaliDate): ?string
    {
        if (!$jalaliDate) return null;
        try {
            $cleaned = str_replace('-', '/', $jalaliDate);
            return \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $cleaned)->toCarbon()->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
