<?php

namespace Modules\Sms\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sms\Entities\SmsTemplate;
use Modules\Sms\Services\SmsManager;

class SmsTemplateController extends Controller
{
    /**
     * دریافت لیست الگوها به صورت JSON
     */
    public function index(Request $request): JsonResponse
    {
        $templates = SmsTemplate::query()
            ->active()
            ->latest('id')
            ->get(['id', 'key', 'title', 'body', 'provider_pattern', 'type', 'created_at']);

        return response()->json([
            'status' => 'success',
            'data'   => $templates,
        ]);
    }

    /**
     * ذخیره یک الگوی پیامک جدید
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'body'             => ['required', 'string', 'max:2000'],
            'provider_pattern' => ['nullable', 'string', 'max:100'],
        ], [
            'title.required' => 'وارد کردن عنوان الگو الزامی است.',
            'body.required'  => 'وارد کردن متن الگو الزامی است.',
        ]);

        $template = SmsTemplate::create([
            'title'            => $validated['title'],
            'body'             => $validated['body'],
            'provider_pattern' => !empty($validated['provider_pattern']) ? trim($validated['provider_pattern']) : null,
            'type'             => SmsTemplate::TYPE_GENERIC,
            'is_active'        => true,
            'meta'             => [
                'created_by' => $request->user()?->id,
            ],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'الگو با موفقیت ذخیره شد.',
                'data'    => $template,
            ], 201);
        }

        return back()->with('status', 'الگوی پیامک با موفقیت ذخیره شد.');
    }

    /**
     * ویرایش الگو
     */
    public function update(Request $request, SmsTemplate $template)
    {
        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'body'             => ['required', 'string', 'max:2000'],
            'provider_pattern' => ['nullable', 'string', 'max:100'],
        ], [
            'title.required' => 'وارد کردن عنوان الگو الزامی است.',
            'body.required'  => 'وارد کردن متن الگو الزامی است.',
        ]);

        $template->update([
            'title'            => $validated['title'],
            'body'             => $validated['body'],
            'provider_pattern' => !empty($validated['provider_pattern']) ? trim($validated['provider_pattern']) : null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'الگو با موفقیت بروزرسانی شد.',
                'data'    => $template,
            ]);
        }

        return back()->with('status', 'الگوی پیامک با موفقیت بروزرسانی شد.');
    }

    /**
     * حذف الگو
     */
    public function destroy(Request $request, SmsTemplate $template)
    {
        $template->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'الگو با موفقیت حذف شد.',
            ]);
        }

        return back()->with('status', 'الگو با موفقیت حذف شد.');
    }

    /**
     * بروزرسانی آنی موجودی از پنل پیامک (پاک کردن کش)
     */
    public function refreshBalance(Request $request, SmsManager $sms): JsonResponse
    {
        $info = $sms->getAccountInfo(fresh: true);

        return response()->json([
            'status' => 'success',
            'data'   => $info,
        ]);
    }
}
