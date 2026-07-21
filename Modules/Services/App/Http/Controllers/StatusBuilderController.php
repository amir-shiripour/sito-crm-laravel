<?php

namespace Modules\Services\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Services\App\Http\Models\Status;
use Modules\Services\App\Services\StatusBuilderService;
use Spatie\Permission\Models\Role;

class StatusBuilderController extends Controller
{
    public function __construct(private StatusBuilderService $svc)
    {
    }

    public function index()
    {
        $this->authorize('status-builder.manage');
        $roles = Role::all();
        $users = User::all();
        $data = array_merge($this->svc->allGrouped(), ['roles' => $roles, 'users' => $users]);
        return view('services::status-builder.index', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        $status = $this->svc->create($this->prepareData($validated));

        return back()->with('success', "وضعیت «{$status->name}» اضافه شد.");
    }

    public function update(Request $request, Status $status)
    {
        $validated = $request->validate($this->validationRules());
        $this->svc->update($status, $this->prepareData($validated));

        return back()->with('success', 'وضعیت ویرایش شد.');
    }

    public function destroy(Status $status)
    {
        $this->authorize('status-builder.manage');
        $status->delete();
        return back()->with('success', 'وضعیت حذف شد.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $this->svc->reorder($request->ids);
        return response()->json(['ok' => true]);
    }

    private function prepareData(array $validated): array
    {
        // لیست ویژگی‌های تخصصی که باید در ستون JSON ذخیره شوند
        $attributeKeys = [
            'converts_to_invoice',
            'locks_invoice',
            'allows_payment',
            'is_successful_payment',
            'is_failed_payment',
        ];

        $attributes = [];
        foreach ($attributeKeys as $key) {
            if (array_key_exists($key, $validated)) {
                $attributes[$key] = filter_var($validated[$key], FILTER_VALIDATE_BOOLEAN);
                unset($validated[$key]);
            } else {
                $attributes[$key] = false;
            }
        }

        $validated['attributes'] = $attributes;

        // اطمینان از فرمت صحیح متغیرهای عمومی
        $validated['is_final'] = filter_var($validated['is_final'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['is_default'] = filter_var($validated['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['is_readonly'] = filter_var($validated['is_readonly'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // اگر نقش‌ها یا کاربران تیک نخورده باشند، به طور پیش‌فرض خالی می‌کنیم
        $validated['allowed_roles'] = $validated['allowed_roles'] ?? null;
        $validated['allowed_users'] = $validated['allowed_users'] ?? null;

        return $validated;
    }

    private function validationRules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'color' => 'required|string|max:20',
            'icon' => 'nullable|string|max:50',
            'type' => 'required|in:project,order,service,invoice,payment',
            'is_final' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'is_readonly' => 'nullable|boolean',
            'allowed_roles' => 'nullable|array',
            'allowed_users' => 'nullable|array',
            // Attributes
            'converts_to_invoice' => 'nullable|boolean',
            'locks_invoice' => 'nullable|boolean',
            'allows_payment' => 'nullable|boolean',
            'is_successful_payment' => 'nullable|boolean',
            'is_failed_payment' => 'nullable|boolean',
        ];
    }
}
