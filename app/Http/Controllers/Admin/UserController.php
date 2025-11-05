<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * لیست کاربران (به‌همراه نقش‌ها)
     */
    public function index(Request $request)
    {
        $users = User::with('roles')->orderByDesc('id')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    /**
     * فرم ایجاد کاربر جدید
     * از همان ویوی edit استفاده می‌کنیم (حالت create)
     */
    public function create()
    {
        $user  = new User();
        $roles = Role::orderBy('name')->pluck('name', 'name'); // ['admin' => 'admin', ...]
        return view('admin.users.edit', compact('user', 'roles'));
//        return view('admin.users.create', ['roles' => $roles]);
    }

    /**
     * ذخیرهٔ کاربر جدید
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','email','max:255','unique:users,email'],
            'mobile'                => ['nullable','string','max:30','unique:users,mobile'],
            'password'              => ['required','confirmed', Rules\Password::defaults()],
            'role'                  => ['required','string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'mobile'   => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        // انتساب نقش (تکی)
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')->with('success', 'کاربر با موفقیت ایجاد شد.');
    }

    /**
     * فرم ویرایش کاربر
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->pluck('name', 'name');
        return view('admin.users.edit', compact('user', 'roles'));
//        return view('admin.users.edit', ['user' => $user, 'roles' => $roles]);
    }

    /**
     * به‌روزرسانی اطلاعات کاربر
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'mobile'                => ['nullable','string','max:30', Rule::unique('users','mobile')->ignore($user->id)],
            'password'              => ['nullable','confirmed', Rules\Password::defaults()],
            'role'                  => ['required','string','exists:roles,name'],
        ]);

        // جلوگیری از حذف نقش super-admin از آخرین سوپرادمین
        if ($user->hasRole('super-admin') && $validated['role'] !== 'super-admin') {
            $superCount = DB::table('model_has_roles')
                ->join('roles','roles.id','=','model_has_roles.role_id')
                ->where('roles.name','super-admin')
                ->count();
            if ($superCount <= 1) {
                return back()->withErrors(['role' => 'نمی‌توان نقش super-admin آخر را حذف کرد.'])->withInput();
            }
        }

        // بروزرسانی فیلدها
        $user->update([
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
        ]);

        // اگر پسورد پر شده بود، عوضش کن
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // آپدیت نقش (تکی)
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')->with('success', 'کاربر با موفقیت به‌روزرسانی شد.');
    }

    /**
     * حذف کاربر
     */
    public function destroy(User $user)
    {
        // خودت را حذف نکن
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'نمی‌توانید حساب کاربری خودتان را حذف کنید.']);
        }

        // جلوگیری از حذف آخرین سوپرادمین
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
}
