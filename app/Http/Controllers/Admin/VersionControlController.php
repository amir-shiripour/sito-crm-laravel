<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VersionLog;
use App\Services\GitHubService;
use Illuminate\Http\Request;

class VersionControlController extends Controller
{
    protected $gitService;

    public function __construct(GitHubService $gitService)
    {
        $this->gitService = $gitService;
    }

    public function index()
    {
        $versions = VersionLog::orderBy('release_date', 'desc')->paginate(15);
        $currentLocal = VersionLog::current();

        // اطلاعات گیت‌هاب در هنگام لود صفحه (اختیاری - برای سرعت بیشتر می‌تواند با Ajax باشد)
        $remoteInfo = session('remote_version_info');

        return view('admin.version_control.index', compact('versions', 'currentLocal', 'remoteInfo'));
    }

    /**
     * چک کردن وضعیت مخزن گیت‌هاب
     */
    public function checkRemote()
    {
        $info = $this->gitService->getLatestRemoteInfo();
        if ($info) {
            session(['remote_version_info' => $info]);
            return redirect()->back()->with('success', 'اطلاعات مخزن با موفقیت دریافت شد.');
        }
        return redirect()->back()->with('error', 'امکان برقراری ارتباط با گیت‌هاب وجود ندارد.');
    }

    /**
     * اجرای عملیات بروزرسانی مستقیم
     */
    public function deployUpdate()
    {
        $result = $this->gitService->performUpdate();

        if ($result['success']) {
            return redirect()->route('admin.version-control.index')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    // ... متدهای قبلی (create, store, edit, update, destroy) دقیقاً طبق فایل قبلی حفظ شوند ...
    public function create() { return view('admin.version_control.create'); }
    public function store(Request $request) { /* منطق ذخیره سازی */ }
    public function edit(VersionLog $versionControl) { return view('admin.version_control.edit', ['version' => $versionControl]); }
    public function update(Request $request, VersionLog $versionControl) { /* منطق آپدیت */ }
    public function destroy(VersionLog $versionControl) { /* منطق حذف */ }
}
