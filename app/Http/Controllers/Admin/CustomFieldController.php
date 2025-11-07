<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomUserField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomFieldController extends Controller
{
    public function index()
    {
        // لیست تخت + صفحه‌بندی (links() کار می‌کند)
        $fields = CustomUserField::orderBy('role_name')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.custom_fields.index', compact('fields'));
    }

    public function create()
    {
        // نقش‌ها را از Spatie یا هر منبعی که دارید بارگذاری کنید
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->pluck('name')->all();
        return view('admin.custom_fields.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role_name'   => ['required','string'],
            'label'       => ['required','string','max:255'],
            'field_type'  => ['required','string'], // همهٔ انواع HTML قابل پشتیبانی
            'is_required' => ['nullable','boolean'],
            // field_name عمداً اعتبارسنجی نمی‌شود تا اختیاری باشد
        ]);

        $data['is_required'] = (bool) ($data['is_required'] ?? false);

        // اگر کاربر field_name نداد، از label بساز و یکتایش کن
        $data['field_name'] = $this->makeUniqueKey(
            $request->input('field_name') ?: $data['label'],
            $data['role_name']
        );

        CustomUserField::create($data);

        return redirect()->route('admin.custom-fields.index')
            ->with('success','فیلد ایجاد شد.');
    }

    public function edit(CustomUserField $field)
    {
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->pluck('name')->all();
        return view('admin.custom_fields.edit', compact('field','roles'));
    }

    public function update(Request $request, CustomUserField $field)
    {
        $data = $request->validate([
            'role_name'   => ['required','string'],
            'label'       => ['required','string','max:255'],
            'field_type'  => ['required','string'],
            'is_required' => ['nullable','boolean'],
            // field_name اختیاری
        ]);

        $data['is_required'] = (bool) ($data['is_required'] ?? false);

        // تصمیم: اگر field_name خالی بود => از label تولید کن
        // اگر پر بود => همان را استفاده کن ولی یکتابودن در نقش را تضمین کن
        $desiredKey = $request->input('field_name') ?: $data['label'];

        // اگر کلید تغییر کرده یا نقش تغییر کرده، نیاز به یکتاسازی داریم
        $needsNewKey = $desiredKey !== $field->field_name || $data['role_name'] !== $field->role_name;

        if ($needsNewKey) {
            $data['field_name'] = $this->makeUniqueKey($desiredKey, $data['role_name'], $field->id);
        } else {
            $data['field_name'] = $field->field_name;
        }

        $field->update($data);

        return redirect()->route('admin.custom-fields.index')
            ->with('success','فیلد بروزرسانی شد.');
    }

    public function destroy(CustomUserField $field)
    {
        $field->delete();
        return back()->with('success','فیلد حذف شد.');
    }

    /**
     * تولید کلید یکتا (snake_case) براساس label/desiredKey برای هر role_name.
     * $ignoreId برای حالت ویرایش تا خود رکورد نادیده گرفته شود.
     */
    private function makeUniqueKey(string $desiredKey, string $roleName, ?int $ignoreId = null): string
    {
        // کلید پایه: اگر خالی شد، پیش‌فرض 'field'
        $base = Str::slug(trim($desiredKey), '_');
        if ($base === '' || $base === null) {
            $base = 'field';
        }

        // رزروها/غیرمجاز را می‌توانید اینجا فیلتر کنید (دلخواه)
        // مثال: جلوگیری از برخورد با کلیدهای سیستمی
        $reserved = ['name','email','password','role'];
        if (in_array($base, $reserved, true)) {
            $base = $base.'_cf';
        }

        $key = $base;
        $i = 1;

        // بررسی یکتا بودن درون همان نقش
        while (
        CustomUserField::where('role_name', $roleName)
            ->where('field_name', $key)
            ->when($ignoreId, fn($q) => $q->where('id','<>',$ignoreId))
            ->exists()
        ) {
            $key = $base.'_'.$i;
            $i++;
        }

        return $key;
    }
}
