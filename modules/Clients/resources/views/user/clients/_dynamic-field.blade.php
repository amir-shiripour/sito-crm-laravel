{{-- clients::user.clients._dynamic-field --}}
@php
    // تعیین ID نهایی فیلد
    $fid = $fid ?? ($field['id'] ?? null);

    // نگاشت فیلدهای سیستمی به پراپرتی‌های لایووایر
    $systemModelMap = [
        'full_name'     => 'full_name',
        'phone'         => 'phone',
        'email'         => 'email',
        'national_code' => 'national_code',
        'notes'         => 'notes',
        'status_id'     => 'status_id', // فیلد سیستمی وضعیت
    ];

    // اگر فیلد سیستمی باشد → مستقیم به پراپرتی، در غیر این صورت → meta.*
    $model = $systemModelMap[$fid] ?? "meta.$fid";

    $type = $field['type'] ?? 'text';

    $opts = [];
    if (!empty($field['options_json'])) {
        $parsed = json_decode($field['options_json'], true);
        if (is_array($parsed)) {
            $opts = $parsed;
        }
    }

    $placeholder = $field['placeholder'] ?? '';

    // کلاس پایه ورودی‌ها (هماهنگ با UI پروژه)
    $baseInputClass = "w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400
                       focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200
                       dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100 dark:focus:bg-gray-900";
@endphp

{{-- textarea --}}
<?php if ($type === 'textarea'): ?>

<textarea
    rows="4"
    wire:model.defer="{{ $model }}"
    placeholder="{{ $placeholder }}"
    class="{{ $baseInputClass }} resize-y min-h-[100px]"
></textarea>

{{-- text / email / number / date --}}
<?php elseif (in_array($type, ['text','email','number','date'], true)): ?>

<input
    type="{{ $type === 'text' ? 'text' : $type }}"
    wire:model.defer="{{ $model }}"
    placeholder="{{ $placeholder }}"
    class="{{ $baseInputClass }}"
/>

{{-- checkbox --}}
<?php elseif ($type === 'checkbox'): ?>

<div class="flex items-center h-full pt-2">
    <input
        type="checkbox"
        id="chk-{{ $fid }}"
        wire:model.defer="{{ $model }}"
        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 transition-colors cursor-pointer"
    />
    <label for="chk-{{ $fid }}" class="mr-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer select-none">
        {{ $placeholder ?: 'فعال / تایید' }}
    </label>
</div>

{{-- radio --}}
<?php elseif ($type === 'radio'): ?>

<div class="flex flex-wrap gap-4 pt-1">
    @foreach($opts as $ov => $ol)
        @php($val = is_string($ov) ? $ov : $ol)
        @php($lab = is_string($ol) ? $ol : $ov)
        <label class="inline-flex items-center gap-2 cursor-pointer group">
            <div class="relative flex items-center">
                <input
                    type="radio"
                    class="peer sr-only"
                    value="{{ $val }}"
                    wire:model.defer="{{ $model }}"
                >
                <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-indigo-600 peer-checked:bg-indigo-600 transition-all dark:border-gray-600"></div>
                <div class="absolute inset-0 m-auto w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
            </div>
            <span class="text-sm text-gray-700 group-hover:text-indigo-600 dark:text-gray-300 transition-colors">{{ $lab }}</span>
        </label>
    @endforeach
</div>

{{-- select --}}
<?php elseif ($type === 'select'): ?>

<div class="relative">
    <select
        wire:model.defer="{{ $model }}"
        class="{{ $baseInputClass }} appearance-none"
        {{ !empty($field['multiple']) ? 'multiple' : '' }}
    >
        @if(empty($field['multiple']))
            <option value="">{{ $placeholder ?: 'انتخاب کنید...' }}</option>
        @endif
        @foreach($opts as $ov => $ol)
            <option value="{{ is_string($ov) ? $ov : $ol }}">
                {{ is_string($ol) ? $ol : $ov }}
            </option>
        @endforeach
    </select>
    @if(empty($field['multiple']))
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    @endif
</div>

{{-- file --}}
<?php elseif ($type === 'file'): ?>

<div class="relative">
    <input
        type="file"
        wire:model="{{ $model }}"
        class="block w-full text-sm text-gray-500
               file:ml-4 file:py-2.5 file:px-4
               file:rounded-full file:border-0
               file:text-xs file:font-semibold
               file:bg-indigo-50 file:text-indigo-700
               hover:file:bg-indigo-100
               dark:file:bg-indigo-900/30 dark:file:text-indigo-300
               cursor-pointer transition-all"
    />
</div>

{{-- status (وضعیت کلاینت) --}}
<?php elseif ($type === 'status'): ?>

<div class="relative">
    <select
        wire:model.defer="{{ $model }}"
        class="{{ $baseInputClass }} appearance-none"
    >
        <option value="">{{ $placeholder ?: 'انتخاب وضعیت...' }}</option>
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

{{-- select-user-by-role --}}
<?php elseif ($type === 'select-user-by-role'): ?>

<div class="relative">
    <select
        wire:model.defer="{{ $model }}"
        class="{{ $baseInputClass }} {{ !empty($field['multiple']) ? 'min-h-[44px]' : '' }}"
        {{ !empty($field['multiple']) ? 'multiple' : '' }}
    >
        @if(empty($field['multiple']))
            <option value="">{{ $placeholder ?: 'انتخاب کاربر...' }}</option>
        @endif
        @foreach($this->usersForRole($field['role'] ?? null) as $u)
            <option value="{{ $u->id }}">{{ $u->name }}</option>
        @endforeach
    </select>
    @if(empty($field['multiple']))
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    @endif
</div>

{{-- fallback --}}
<?php else: ?>

<input
    type="text"
    wire:model.defer="{{ $model }}"
    placeholder="{{ $placeholder }}"
    class="{{ $baseInputClass }}"
/>

<?php endif; ?>

@error($model)
<div class="flex items-center gap-1 text-xs font-medium text-red-600 mt-1.5">
    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ $message }}
</div>
@enderror
