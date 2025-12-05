{{-- modules/Clients/resources/views/widgets/client-quick-create.blade.php --}}

@php
    $labelSingular = config('clients.labels.singular', 'مشتری');
@endphp

<div
    class="h-full space-y-4 text-sm text-gray-800 dark:text-gray-200"
>
    {{-- هدر ویجت --}}
    <div class="flex items-center justify-between gap-2">
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                ایجاد سریع {{ $labelSingular }}
            </h3>

        </div>
    </div>

    {{-- بدنه ویجت: فرم Livewire به صورت inline --}}
    @livewire(\Modules\Clients\App\Livewire\ClientForm::class, [
    // برای ایجاد جدید؛ نیازی به client موجود نیست
    'client'        => new \Modules\Clients\Entities\Client(),
    'formKey'       => null,
    'asQuickWidget' => true,   // یعنی از مسیر quick استفاده شود
    'isQuickMode'   => true,   // ولیدیشن و ذخیره‌سازی حالت quick
    'forWidget'     => true,   // ویوی اختصاصی ویجت را رندر کن
    ], key('client-quick-create-widget'))
</div>
