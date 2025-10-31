<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * نمایش لیست تمام کاربران
     */
    public function index()
    {
        // کاربران را می‌گیریم و صفحه‌بندی می‌کنیم
        $users = User::paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * نمایش فرم ویرایش یک کاربر خاص
     */
    public function edit(User $user)
    {
        // تمام نقش‌های موجود را می‌گیریم تا در فرم نمایش دهیم
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * آپدیت کردن اطلاعات کاربر در دیتابیس
     */
    public function update(Request $request, User $user)
    {
        // اعتبارسنجی داده‌های ورودی
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id) // ایمیل نباید تکراری باشد، جز برای خود این کاربر
            ],
            'mobile' => ['nullable', 'string', 'regex:/^0[0-9]{9,10}$/'], // یک ولیدیشن ساده برای موبایل (مثال: 09123456789)
            'role' => ['required', 'string', Rule::exists('roles', 'name')] // نقش باید در جدول نقش‌ها وجود داشته باشد
        ]);

        // آپدیت کردن فیلدهای اصلی کاربر
        $user->update($request->only('name', 'email', 'mobile'));

        // آپدیت کردن نقش کاربر با استفاده از پکیج Spatie
        // syncRoles نقش‌های قبلی را حذف و نقش جدید را جایگزین می‌کند
        $user->syncRoles([$request->role]);

        // بازگشت به لیست کاربران به همراه پیام موفقیت
        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }
}

