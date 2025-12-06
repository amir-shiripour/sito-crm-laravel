<?php

namespace Modules\Workflows\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Workflows\Entities\Workflow;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('workflows.view');

        $q = Workflow::query()->withCount('stages');

        if ($search = $request->get('q')) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $workflows = $q->orderBy('created_at', 'desc')->paginate(20);

        return view('workflows::user.workflows.index', compact('workflows'));
    }

    public function show(Workflow $workflow)
    {
        Gate::authorize('workflows.view');

        $workflow->load(['stages.actions']);

        return view('workflows::user.workflows.show', compact('workflow'));
    }
}
