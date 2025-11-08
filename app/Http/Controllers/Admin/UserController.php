<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomUserField;
use App\Models\User;
use App\Models\UserCustomValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')->orderByDesc('id')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $user  = new User();
        $roles = Role::orderBy('name')->pluck('display_name', 'name');
        $selectedRole = ''; // جلوگیری از null
        $customFieldsByRole = CustomUserField::orderBy('role_name')->get()->groupBy('role_name');

        return view('admin.users.edit', compact('user','roles','selectedRole','customFieldsByRole'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'mobile'   => ['nullable','string','max:30','unique:users,mobile'],
            'password' => ['required','confirmed', Rules\Password::defaults()],
            'role'     => ['required','string', 'exists:roles,name'],
        ]);

        $roleName = $validated['role'];
        $fields   = $this->fieldsForRole($roleName);

        $request->validate($this->dynamicRules($fields)); // ولیدیشن داینامیک

        DB::transaction(function () use ($validated, $request, $fields, $roleName, &$user) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'mobile'   => $validated['mobile'] ?? null,
                'password' => Hash::make($validated['password']),
            ]);

            $user->syncRoles([$roleName]);

            // مقادیر سفارشی (با پشتیبانی فایل)
            $this->persistCustomValues($user, $fields, $request);
        });

        return redirect()->route('admin.users.index')->with('success', 'کاربر با موفقیت ایجاد شد.');
    }

    public function edit(User $user)
    {
        $user->loadMissing(['roles','customValues']);

        $roles = Role::orderBy('name')->pluck('display_name', 'name');
        $selectedRole = optional($user->roles->first())->name ?? '';
        $customFieldsByRole = CustomUserField::orderBy('role_name')->get()->groupBy('role_name');

        return view('admin.users.edit', compact('user','roles','customFieldsByRole','selectedRole'));
    }


    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'mobile'   => ['nullable','string','max:30', Rule::unique('users','mobile')->ignore($user->id)],
            'password' => ['nullable','confirmed', Rules\Password::defaults()],
            'role'     => ['required','string','exists:roles,name'],
        ]);

        // جلوگیری از حذف آخرین سوپرادمین
        if ($user->hasRole('super-admin') && $validated['role'] !== 'super-admin') {
            $superCount = DB::table('model_has_roles')
                ->join('roles','roles.id','=','model_has_roles.role_id')
                ->where('roles.name','super-admin')
                ->count();
            if ($superCount <= 1) {
                return back()->withErrors(['role' => 'نمی‌توان نقش super-admin آخر را حذف کرد.'])->withInput();
            }
        }

        $roleName = $validated['role'];
        $fields   = $this->fieldsForRole($roleName);

        $request->validate($this->dynamicRules($fields)); // ولیدیشن داینامیک

        DB::transaction(function () use ($user, $validated, $fields, $request, $roleName) {
            $user->update([
                'name'   => $validated['name'],
                'email'  => $validated['email'],
                'mobile' => $validated['mobile'] ?? null,
            ]);

            if (!empty($validated['password'])) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            $user->syncRoles([$roleName]);

            // upsert مقادیر سفارشی (با فایل)
            $this->persistCustomValues($user, $fields, $request);
        });

        return redirect()->route('admin.users.index')->with('success', 'کاربر با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'نمی‌توانید حساب کاربری خودتان را حذف کنید.']);
        }

        if ($user->hasRole('super-admin')) {
            $superCount = DB::table('model_has_roles')
                ->join('roles','roles.id','=','model_has_roles.role_id')
                ->where('roles.name','super-admin')
                ->count();

            if ($superCount <= 1) {
                return back()->withErrors(['user' => 'نمی‌توان آخرین سوپرادمین را حذف کرد.']);
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'کاربر حذف شد.');
    }

    /* ===================== Private helpers ===================== */

    private function fieldsForRole(string $roleName): Collection
    {
        return CustomUserField::where('role_name', $roleName)->orderBy('id')->get();
    }

    private function dynamicRules(Collection $fields): array
    {
        $rules = [];

        foreach ($fields as $f) {
            $key  = 'custom.' . $f->field_name;
            $type = strtolower($f->field_type ?? 'text');
            $meta = is_array($f->meta ?? null) ? $f->meta : (is_string($f->meta ?? null) ? json_decode($f->meta, true) : []);
            $meta = $meta ?: [];

            $base = ($f->is_required ?? false) ? ['required'] : ['nullable'];

            // چرا: ولیدیشن نوع‌ها
            switch ($type) {
                case 'number':
                    $base[] = 'numeric';
                    break;
                case 'date':
                    $base[] = 'date';
                    break;
                case 'email':
                    $base[] = 'email';
                    break;
                case 'file':
                    $fileRules = ['file'];
                    if (!empty($meta['mimes']) && is_array($meta['mimes'])) {
                        $fileRules[] = 'mimes:' . implode(',', $meta['mimes']);
                    } elseif (!empty($meta['mimes']) && is_string($meta['mimes'])) {
                        $fileRules[] = 'mimes:' . $meta['mimes'];
                    }
                    if (!empty($meta['max'])) {
                        // max بر حسب کیلوبایت
                        $fileRules[] = 'max:' . intval($meta['max']);
                    }
                    $base = array_merge($base, $fileRules);
                    break;
                case 'checkbox':
                    // checkbox چندانتخابی
                    $base[] = 'array';
                    if (!empty($meta['options']) && is_array($meta['options'])) {
                        $base['custom.' . $f->field_name . '.*'] = ['in:' . implode(',', array_values($meta['options']))];
                    }
                    break;
                case 'select':
                case 'radio':
                    if (!empty($meta['options']) && is_array($meta['options'])) {
                        $base[] = 'in:' . implode(',', array_values($meta['options']));
                    } else {
                        $base[] = 'string';
                    }
                    break;
                default:
                    $base[] = 'string';
            }

            $rules[$key] = $base;

            // اگر ruleهای اضافه به‌صورت آرایه در DB ذخیره شده
            if (!empty($f->rules) && is_array($f->rules)) {
                $rules[$key] = array_merge($rules[$key], $f->rules);
            }
        }

        return $rules;
    }

    private function persistCustomValues(User $user, Collection $fields, Request $request): void
    {
        foreach ($fields as $f) {
            $type = strtolower($f->field_type ?? 'text');

            if ($type === 'file') {
                // چرا: فایل از input با نام custom[field_name]
                $file = $request->file('custom.' . $f->field_name);
                if ($file) {
                    // حذف فایل قبلی اگر وجود داشت
                    $prev = UserCustomValue::where('user_id', $user->id)
                        ->where('field_name', $f->field_name)
                        ->first();

                    if ($prev && $prev->value && Storage::disk('public')->exists($prev->value)) {
                        Storage::disk('public')->delete($prev->value);
                    }

                    // ذخیره فایل جدید
                    $path = $file->store('user-custom', 'public');

                    UserCustomValue::updateOrCreate(
                        ['user_id' => $user->id, 'field_name' => $f->field_name],
                        ['value' => $path],
                    );
                } else {
                    // اگر فایل جدید نیامده، مقدار قبلی را دست‌نخورده بگذار
                    if (!UserCustomValue::where('user_id', $user->id)->where('field_name', $f->field_name)->exists()) {
                        UserCustomValue::create([
                            'user_id'    => $user->id,
                            'field_name' => $f->field_name,
                            'value'      => null,
                        ]);
                    }
                }
                continue;
            }

            // سایر انواع
            $raw = $request->input('custom.' . $f->field_name);

            if ($type === 'checkbox' && is_array($raw)) {
                // ذخیره آرایه به صورت JSON
                $value = json_encode(array_values($raw), JSON_UNESCAPED_UNICODE);
            } else {
                $value = $raw;
            }

            UserCustomValue::updateOrCreate(
                ['user_id' => $user->id, 'field_name' => $f->field_name],
                ['value' => $value],
            );
        }
    }
}
