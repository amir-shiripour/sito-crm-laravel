{{-- clients::user.clients._quick-field --}}
@php
    $type = $field['type'] ?? 'text';

    // options برای select / radio
    $opts = [];
    if (!empty($field['options_json'])) {
        $parsed = json_decode($field['options_json'], true);
        if (is_array($parsed)) {
            $opts = $parsed;
        }
    }

    // placeholder سفارشی (اگر در اسکیمای فرم ست شده باشد)
    $placeholder = $field['placeholder'] ?? '...';

    // استایل مشترک با فرم اصلی برای یکپارچگی
    $baseInputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                       focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 transition-all duration-200
                       dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900 dark:focus:border-emerald-500/50";
@endphp

{{-- متن ساده: text / email / number / date --}}
<?php if (in_array($type, ['text','email','number','date'], true)) : ?>
<input
    type="{{ $type === 'text' ? 'text' : $type }}"
    class="{{ $baseInputClass }}"
    wire:model.defer="quick.{{ $fid }}"
    placeholder="{{ $placeholder }}"
>

{{-- textarea --}}
<?php elseif ($type === 'textarea') : ?>
<textarea
    rows="2"
    class="{{ $baseInputClass }} resize-none"
    wire:model.defer="quick.{{ $fid }}"
    placeholder="{{ $placeholder }}"
></textarea>

{{-- checkbox --}}
<?php elseif ($type === 'checkbox') : ?>
<div class="flex items-center h-full pt-1.5">
    <input
        type="checkbox"
        id="qc-chk-{{ $fid }}"
        value="1"
        wire:model.defer="quick.{{ $fid }}"
        class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 cursor-pointer transition-colors"
    />
    <label
        for="qc-chk-{{ $fid }}"
        class="mr-2 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none"
    >
        {{ $placeholder !== '...' ? $placeholder : 'فعال / انتخاب' }}
    </label>
</div>

{{-- radio --}}
<?php elseif ($type === 'radio') : ?>
<div class="flex flex-wrap gap-3 pt-1">
    @foreach($opts as $ov => $ol)
        @php($val = is_string($ov) ? $ov : $ol)
        @php($lab = is_string($ol) ? $ol : $ov)
        <label class="inline-flex items-center gap-2 cursor-pointer group">
            <input
                type="radio"
                class="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800"
                wire:model.defer="quick.{{ $fid }}"
                value="{{ $val }}"
            >
            <span class="text-xs text-gray-700 group-hover:text-emerald-600 dark:text-gray-300 transition-colors">
                {{ $lab }}
            </span>
        </label>
    @endforeach
</div>

{{-- select عمومی --}}
<?php elseif ($type === 'select') : ?>
<div class="relative">
    <select
        class="{{ $baseInputClass }} appearance-none"
        wire:model.defer="quick.{{ $fid }}"
    >
        <option value="">{{ $placeholder !== '...' ? $placeholder : 'انتخاب کنید...' }}</option>
        @foreach($opts as $ov => $ol)
            <option value="{{ is_string($ov) ? $ov : $ol }}">
                {{ is_string($ol) ? $ol : $ov }}
            </option>
        @endforeach
    </select>
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
</div>

{{-- select-user-by-role --}}
<?php elseif ($type === 'select-user-by-role') : ?>
<div class="relative">
    <select
        class="{{ $baseInputClass }} appearance-none"
        wire:model.defer="quick.{{ $fid }}"
    >
        <option value="">{{ $placeholder !== '...' ? $placeholder : 'انتخاب کاربر...' }}</option>
        @foreach($this->usersForRole($field['role'] ?? null) as $u)
            <option value="{{ $u->id }}">{{ $u->name }}</option>
        @endforeach
    </select>
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
</div>

{{-- status در ایجاد سریع --}}
<?php elseif ($type === 'status') : ?>
<div class="relative">
    <select
        class="{{ $baseInputClass }} appearance-none"
        wire:model.defer="quick.{{ $fid }}"
    >
        <option value="">{{ $placeholder !== '...' ? $placeholder : 'انتخاب وضعیت...' }}</option>
        @foreach($availableStatuses ?? [] as $st)
            <option value="{{ $st['id'] }}">{{ $st['label'] }}</option>
        @endforeach
    </select>
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
</div>

{{-- سایر انواع (fallback) --}}
<?php else : ?>
<input
    type="text"
    class="{{ $baseInputClass }}"
    wire:model.defer="quick.{{ $fid }}"
    placeholder="{{ $placeholder }}"
>
<?php endif; ?>

@error("quick.".$fid)
<div class="text-xs text-red-600 mt-1 mr-1 flex items-center gap-1">
    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    {{ $message }}
</div>
@enderror
