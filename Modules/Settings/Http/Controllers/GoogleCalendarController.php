<?php

namespace Modules\Settings\Http\Controllers;

use App\Models\GoogleCalendarToken;
use App\Services\GoogleCalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class GoogleCalendarController extends Controller
{
    protected GoogleCalendarService $googleService;

    public function __construct(GoogleCalendarService $googleService)
    {
        $this->middleware('auth');
        $this->googleService = $googleService;
    }

    /**
     * شروع فرایند OAuth — هدایت کاربر به گوگل
     */
    public function connect(Request $request)
    {
        if (!$this->googleService->isConfigured()) {
            return redirect()->route('settings.index')
                ->with('error', 'لطفاً ابتدا Client ID و Client Secret را در تنظیمات گوگل وارد و ذخیره کنید.')
                ->withFragment('widgets');
        }

        $state = Str::random(32);
        session(['google_oauth_state' => $state]);

        $authUrl = $this->googleService->getAuthUrl($state);
        if (!$authUrl) {
            return redirect()->route('settings.index')
                ->with('error', 'خطا در ایجاد لینک اتصال به گوگل.')
                ->withFragment('widgets');
        }

        return redirect()->away($authUrl);
    }

    /**
     * دریافت Callback از گوگل پس از تأیید کاربر
     */
    public function callback(Request $request)
    {
        $state = $request->query('state');
        $savedState = session('google_oauth_state');
        session()->forget('google_oauth_state');

        if (empty($state) || $state !== $savedState) {
            return redirect()->route('settings.index')
                ->with('error', 'اعتبارسنجی نشست (State) گوگل با خطا مواجه شد.')
                ->withFragment('widgets');
        }

        $code = $request->query('code');
        if (empty($code)) {
            return redirect()->route('settings.index')
                ->with('error', 'کد تایید اولیه از گوگل دریافت نشد.')
                ->withFragment('widgets');
        }

        $token = $this->googleService->handleCallback($code, auth()->id());
        if (!$token) {
            return redirect()->route('settings.index')
                ->with('error', 'اتصال به حساب گوگل با خطا مواجه شد.')
                ->withFragment('widgets');
        }

        return redirect()->route('settings.index')
                ->with('success', "حساب گوگل ({$token->email}) با موفقیت به سیستم متصل گردید.")
                ->withFragment('widgets');
    }

    /**
     * قطع اتصال حساب گوگل
     */
    public function disconnect(Request $request)
    {
        $this->googleService->disconnect();

        return redirect()->route('settings.index')
            ->with('success', 'اتصال حساب گوگل با موفقیت قطع گردید.')
            ->withFragment('widgets');
    }

    /**
     * دریافت لیست تقویم‌های حساب متصل (برای AJAX)
     */
    public function listCalendars(): JsonResponse
    {
        $token = GoogleCalendarToken::where('is_active', true)->latest()->first();
        if (!$token) {
            return response()->json(['success' => false, 'calendars' => []]);
        }

        $calendars = $this->googleService->listCalendars($token);
        return response()->json([
            'success'      => true,
            'calendars'    => $calendars,
            'selected_ids' => $token->calendar_ids ?: ['primary'],
        ]);
    }

    /**
     * ذخیره لیست تقویم‌های انتخاب شده توسط مدیر
     */
    public function saveCalendars(Request $request)
    {
        $token = GoogleCalendarToken::where('is_active', true)->latest()->first();
        if (!$token) {
            return redirect()->route('settings.index')
                ->with('error', 'هیچ حساب گوگل فعالی یافت نشد.')
                ->withFragment('widgets');
        }

        $calendarIds = $request->input('calendar_ids', ['primary']);
        if (!is_array($calendarIds) || empty($calendarIds)) {
            $calendarIds = ['primary'];
        }

        $token->update(['calendar_ids' => $calendarIds]);

        return redirect()->route('settings.index')
            ->with('success', 'تقویم‌های انتخابی گوگل با موفقیت ذخیره شدند.')
            ->withFragment('widgets');
    }

    /**
     * ایمپورت دستی فایل‌های iCal / ICS / ZIP رویدادهای گوگل
     */
    public function importFile(Request $request)
    {
        $request->validate([
            'ical_file' => 'required|file|max:20480', // تا ۲۰ مگابایت
        ], [
            'ical_file.required' => 'لطفاً یک فایل iCal (.ics / .ical / .zip) انتخاب کنید.',
            'ical_file.file'     => 'فایل انتخاب‌شده نامعتبر است.',
            'ical_file.max'      => 'حجم فایل نباید بیشتر از ۲۰ مگابایت باشد.',
        ]);

        $file = $request->file('ical_file');
        $importService = app(\App\Services\GoogleCalendarImportService::class);

        $result = $importService->importFile($file, auth()->id());

        if (!empty($result['success'])) {
            $msg = "ایمپورت دستی گوگل کلندر با موفقیت انجام گردید. (تعداد {$result['total']} رویداد پردازش شد)";
            return redirect()->route('settings.index')
                ->with('success', $msg)
                ->withFragment('widgets');
        }

        return redirect()->route('settings.index')
            ->with('error', $result['message'] ?? 'خطا در پردازش فایل iCal.')
            ->withFragment('widgets');
    }

    /**
     * پاکسازی تمام رویدادهای ایمپورت‌شده
     */
    public function clearImported(Request $request)
    {
        $importService = app(\App\Services\GoogleCalendarImportService::class);
        $deleted = $importService->clearAllImportedEvents();

        return redirect()->route('settings.index')
            ->with('success', "تعداد {$deleted} رویداد ایمپورت‌شده گوگل کلندر پاکسازی شد.")
            ->withFragment('widgets');
    }
}
