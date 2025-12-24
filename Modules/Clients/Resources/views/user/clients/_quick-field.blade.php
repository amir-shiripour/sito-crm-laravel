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

{{-- پسورد در ایجاد سریع --}}
@if($type === 'password')
    <div class="flex items-center gap-2" x-data="{ show: false }">
        <div class="relative flex-1">
            <input
                x-bind:type="show ? 'text' : 'password'"
                class="{{ $baseInputClass }} pr-9 font-mono"
                wire:model.defer="password"
                placeholder="{{ $placeholder !== '...' ? $placeholder : 'رمز عبور امن...' }}"
            >
            <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                @click="show = !show"
            >
                <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                             -1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7
                             .51-1.626 1.48-3.059 2.75-4.155M9.88 9.88a3 3 0 014.24 4.24
                             M6.1 6.1L4 4m0 0l16 16m-2.1-2.1L20 20"/>
                </svg>
            </button>
        </div>

        <button
            type="button"
            wire:click="generatePassword"
            class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-medium border border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 hover:border-emerald-300 dark:border-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200 dark:hover:bg-emerald-800/70 transition-colors"
        >
            ساخت خودکار
        </button>
    </div>
    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
        حداقل ۸ کاراکتر و شامل حروف و اعداد باشد. در صورت کلیک روی «ساخت خودکار»، رمز امن تولید و در همین فیلد پر می‌شود.
    </p>

    @error('password')
    <div class="text-xs text-red-600 mt-1 mr-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ $message }}
    </div>
    @enderror

    {{-- متن ساده: text / email / number / date --}}
@elseif(in_array($type, ['text','email','number','date'], true))
    <input
        type="{{ $type === 'text' ? 'text' : $type }}"
        class="{{ $baseInputClass }}"
        wire:model.defer="quick.{{ $fid }}"
        placeholder="{{ $placeholder }}"
    >

    {{-- textarea --}}
@elseif($type === 'textarea')
    <textarea
        rows="2"
        class="{{ $baseInputClass }} resize-none"
        wire:model.defer="quick.{{ $fid }}"
        placeholder="{{ $placeholder }}"
    ></textarea>

    {{-- checkbox --}}
@elseif($type === 'checkbox')
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
@elseif($type === 'radio')
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
@elseif($type === 'select')
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
@elseif($type === 'select-user-by-role')
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
@elseif($type === 'status')
    <div class="relative">
        <select
            class="{{ $baseInputClass }} appearance-none"
            wire:model.defer="quick.{{ $fid }}"
        >
            <option value="">{{ $placeholder !== '...' ? $placeholder : 'انتخاب وضعیت...' }}</option>
            @foreach(($availableStatuses ?? []) as $st)
                <option value="{{ is_array($st) ? $st['id'] : $st->id }}">
                    {{ is_array($st) ? ($st['label'] ?? $st['key']) : ($st->label ?? $st->key) }}
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

    {{-- سایر انواع (fallback) --}}
@else
    <input
        type="text"
        class="{{ $baseInputClass }}"
        wire:model.defer="quick.{{ $fid }}"
        placeholder="{{ $placeholder }}"
    >
@endif

{{-- ارور عمومی فقط برای فیلدهایی که password نیستند --}}
@if($type !== 'password')
    @error("quick.".$fid)
    <div class="text-xs text-red-600 mt-1 mr-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ $message }}
    </div>
    @enderror
@endif
