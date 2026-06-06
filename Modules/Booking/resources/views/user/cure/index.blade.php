@extends('layouts.user')

@section('content')
    <style>
        [x-cloak] { display: none !important; }

        /* ── Tooth paths ── */
        .tooth-path {
            cursor: pointer;
            transition: fill .14s ease, stroke .14s ease, filter .14s ease;
            stroke-width: 1.5px;
            vector-effect: non-scaling-stroke;
        }
        .tooth-selected {
            fill: #6366f1;
            stroke: #4338ca;
            filter: drop-shadow(0 2px 6px rgba(99,102,241,.55));
        }
        .tooth-in-plan {
            fill: #d1fae5;
            stroke: #10b981;
        }
        .dark .tooth-in-plan {
            fill: #064e3b;
            stroke: #34d399;
        }
        .tooth-unselected {
            fill: #fff;
            stroke: #cbd5e1;
        }
        .dark .tooth-unselected {
            fill: #334155;
            stroke: #475569;
        }
        .tooth-unselected:hover {
            fill: #eef2ff;
            stroke: #818cf8;
        }
        .dark .tooth-unselected:hover {
            fill: #312e81;
            stroke: #818cf8;
        }

        /* ── Service list item ── */
        .svc-active {
            background: linear-gradient(90deg, #eef2ff 0%, #f5f3ff 100%);
            border-right: 3px solid #6366f1;
        }
        .dark .svc-active {
            background: linear-gradient(90deg, rgba(99,102,241,.14) 0%, rgba(139,92,246,.09) 100%);
            border-right-color: #818cf8;
        }

        /* ── Animations ── */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(5px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim-slide-up { animation: slideUp .22s ease forwards; }
        .anim-fade-up  { animation: fadeUp  .18s ease forwards; }

        /* ── Row hover reveal ── */
        .plan-row:hover .row-del { opacity: 1; }
        .row-del { opacity: 0; transition: opacity .18s; }

        /* ── Thin scrollbar ── */
        .sc-thin::-webkit-scrollbar { width: 4px; height: 4px; }
        .sc-thin::-webkit-scrollbar-track { background: transparent; }
        .sc-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
        .dark .sc-thin::-webkit-scrollbar-thumb { background: #475569; }
    </style>

    <div
        x-data="treatmentPlanApp(@js($servicesJs))"
        x-cloak
        class="space-y-5 pb-20"
        dir="rtl"
    >

        {{-- ══════════════════ TOP ACTION BAR ══════════════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-4
                    flex flex-col sm:flex-row sm:items-center justify-between gap-4">

            {{-- Title --}}
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl shrink-0 flex items-center justify-center"
                     style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 14px rgba(99,102,241,.35);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"
                         stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-bold text-gray-900 dark:text-white">ایجاد طرح درمان</h1>
                        <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400
                                     text-[10px] font-bold rounded-md uppercase tracking-wide">پیش‌نویس</span>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">دندان → سرویس → افزودن به طرح</p>
                </div>
            </div>

            {{-- Right controls --}}
            <div class="flex items-center gap-3 flex-wrap">

                {{-- Patient name --}}
                <div class="relative">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <input x-model="patientName" type="text" placeholder="نام بیمار…"
                           class="pr-9 pl-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600
                                  bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400
                                  focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100
                                  dark:focus:ring-indigo-900/30 w-44 transition-all"/>
                </div>

                {{-- Live stats --}}
                <div class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl
                            border border-indigo-100 dark:border-indigo-800/30">
                    <span class="text-sm font-black text-indigo-600 dark:text-indigo-400" x-text="planItems.length"></span>
                    <span class="text-xs text-indigo-500 dark:text-indigo-400">مورد</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl
                            border border-emerald-100 dark:border-emerald-800/30">
                    <span class="text-sm font-black text-emerald-600 dark:text-emerald-400"
                          x-text="formatPrice(total)"></span>
                    <span class="text-xs text-emerald-500 dark:text-emerald-400">تومان</span>
                </div>

                {{-- Save draft --}}
                <button class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600
                               bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm font-medium
                               hover:border-indigo-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    ذخیره پیش‌نویس
                </button>

                {{-- Confirm --}}
                <button :disabled="planItems.length === 0"
                        :class="planItems.length === 0 ? 'opacity-50 cursor-not-allowed' :
                                'hover:shadow-lg hover:shadow-indigo-200/50 dark:hover:shadow-indigo-900/30 hover:scale-[1.02]'"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-bold transition-all"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    تأیید طرح
                </button>
            </div>
        </div>

        {{-- ══════════════════ MAIN GRID ══════════════════ --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">

            {{-- ─────────────────────────────────────────
                 LEFT COL (2/3): Dental Chart + Add Strip
                 ───────────────────────────────────────── --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- Dental chart card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

                    {{-- Chart header --}}
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex-wrap gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-5 rounded-full bg-rose-500 shrink-0"></span>
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">نقشه دندانی</h2>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                (<span x-text="selectedTeeth.length"></span> انتخابی)
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="selectJaw('upper')"
                                    :class="preset==='upper'
                                        ? 'bg-indigo-600 text-white shadow-md'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">فک بالا</button>
                            <button @click="selectJaw('lower')"
                                    :class="preset==='lower'
                                        ? 'bg-indigo-600 text-white shadow-md'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">فک پایین</button>
                            <button @click="selectAllTeeth()"
                                    :class="preset==='all'
                                        ? 'bg-violet-600 text-white shadow-md'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all">همه</button>
                            <button @click="resetTeeth()"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-600
                                           hover:bg-rose-100 dark:bg-rose-900/20 dark:text-rose-400 transition-all">
                                پاک‌سازی
                            </button>
                        </div>
                    </div>

                    {{-- SVG odontogram --}}
                    <div class="px-4 pt-4 pb-1 relative">
                        {{-- Selection counter --}}
                        <div class="absolute top-6 left-6 z-10 bg-white/90 dark:bg-gray-800/90 backdrop-blur
                                    px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm text-center">
                            <span class="text-[10px] text-gray-400 uppercase font-bold block">انتخاب</span>
                            <span class="text-xl font-black text-indigo-600 dark:text-indigo-400"
                                  x-text="selectedTeeth.length"></span>
                        </div>

                        {{-- Legend --}}
                        <div class="absolute top-6 right-6 z-10 flex flex-col gap-1.5
                                    bg-white/90 dark:bg-gray-800/90 backdrop-blur px-3 py-2 rounded-xl
                                    border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-sm bg-indigo-500 shrink-0"></span>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">انتخاب شده</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-sm bg-emerald-200 border border-emerald-400 shrink-0"></span>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">در طرح</span>
                            </div>
                        </div>

                        <svg viewBox="0 0 757 335" fill="none" xmlns="http://www.w3.org/2000/svg"
                             class="w-full h-auto">

                            {{-- Gum background --}}
                            <path d="M664.391 77.986L620.365 192.964C620.365 192.964 573.049 297.839 370.214 288.945C204.972 281.7 172.575 231.809 152.082 200.962C129.782 167.395 86.042 79.986 86.042 79.986C86.042 79.986 61.742 49.18 162.088 59.986C262.434 70.792 503.294 62.986 503.294 62.986C503.294 62.986 686.948 32.372 664.391 77.986Z"
                                  class="fill-rose-200/40 dark:fill-rose-900/20 stroke-rose-300/30"/>

                            {{-- ── Upper teeth 1–14 ── --}}
                            <path @click="toggle(1)"  :class="is(1)"  class="tooth-path" d="M639.143 65.989C639.143 65.989 641.715 84.399 638.375 95.983C637.197 99.1773 635.112 101.958 632.375 103.983C629.894 105.659 627.205 107.004 624.375 107.983C620.743 109.496 619.323 108.345 616.763 102.167C614.772 97.367 613.296 86.918 612.34 76.135C612.714 76.383 639.143 65.989 639.143 65.989Z"/>
                            <path @click="toggle(2)"  :class="is(2)"  class="tooth-path" d="M610.086 77.255C610.086 77.255 592.752 81.791 585.828 83.978C585.55 83.902 589.548 106.697 594.349 115.978C595.036 117.449 596.2 118.645 597.653 119.371C599.105 120.097 600.76 120.311 602.349 119.978C604.495 119.3 606.519 118.288 608.349 116.978C609.909 115.624 613.296 112.305 613.349 107.978C613.507 96.76 610.3 78.658 610.086 77.255Z"/>
                            <path @click="toggle(3)"  :class="is(3)"  class="tooth-path" d="M583.6 85.1C583.6 85.1 588.042 103.667 589.4 116.408C590.164 123.567 587.729 128.994 583.342 131.98C580.829 133.35 578.136 134.359 575.342 134.98C570.422 135.751 566.842 132.605 565.336 126.98C562.555 116.618 560.213 98.5 559.456 91.79C559.779 91.714 583.6 85.1 583.6 85.1Z"/>
                            <path @click="toggle(4)"  :class="is(4)"  class="tooth-path" d="M557.233 92.9C557.233 92.9 559.689 111.173 561.533 125.936C562.621 134.657 560.053 142.154 554.333 143.974C550.242 145.274 542.028 149.589 539.324 148.974C534.841 147.953 532.796 144.638 532.154 138.72C531.268 130.544 530.426 114.92 530.082 97.344C530.256 97.858 557.233 92.9 557.233 92.9Z"/>
                            <path @click="toggle(5)"  :class="is(5)"  class="tooth-path" d="M526.844 97.342C526.844 97.342 529.244 131.742 527.309 145.973C525.499 159.26 505.441 161.233 500.292 161.973C495.143 162.713 491.625 159.917 491.748 149.497C491.872 139.009 492.275 100.668 492.275 100.668C503.85 100.193 515.391 99.0822 526.844 97.342Z"/>
                            <path @click="toggle(6)"  :class="is(6)"  class="tooth-path" d="M489.728 101.772C489.728 101.772 489.728 138.498 487.068 157.741C485.99 165.532 481.968 170.714 477.278 171.968C472.675 173.255 467.996 174.257 463.27 174.968C457.563 175.786 450.277 169.959 450.261 158.384C450.245 146.724 449.173 104.373 449.173 104.373C449.173 104.373 472.1 104.723 489.728 101.772Z"/>
                            <path @click="toggle(7)"  :class="is(7)"  class="tooth-path" d="M445.125 103.875C445.125 103.875 448.468 152.032 446.566 164.964C444.679 177.791 439.326 183.698 428.86 184.364C418.785 185.166 408.663 185.166 398.588 184.364C388.049 183.423 380.87 177.788 381.298 166.738C381.728 155.611 378.791 122.372 386.084 106.638C386.236 106.514 427.233 106.68 445.125 103.875Z"/>
                            <path @click="toggle(8)"  :class="is(8)"  class="tooth-path" d="M313.915 103.875C313.915 103.875 310.574 152.032 312.475 164.964C314.361 177.791 319.711 183.698 330.17 184.364C340.239 185.166 350.355 185.166 360.424 184.364C370.956 183.423 378.131 177.788 377.704 166.738C377.274 155.611 380.21 122.372 372.921 106.638C372.77 106.514 331.8 106.68 313.915 103.875Z"/>
                            <path @click="toggle(9)"  :class="is(9)"  class="tooth-path" d="M269.339 101.772C269.339 101.772 269.339 138.498 271.997 157.741C273.075 165.532 277.092 170.714 281.781 171.968C286.382 173.255 291.057 174.256 295.781 174.968C301.481 175.786 308.766 169.959 308.781 158.384C308.797 146.724 309.868 104.373 309.868 104.373C309.868 104.373 286.957 104.723 269.339 101.772Z"/>
                            <path @click="toggle(10)" :class="is(10)" class="tooth-path" d="M232.245 97.342C232.245 97.342 229.845 131.742 231.781 145.973C233.589 159.26 253.635 161.233 258.781 161.973C263.927 162.713 267.443 159.917 267.32 149.497C267.196 139.009 266.793 100.668 266.793 100.668C255.225 100.192 243.691 99.0821 232.245 97.342Z"/>
                            <path @click="toggle(11)" :class="is(11)" class="tooth-path" d="M201.874 92.9C201.874 92.9 199.42 111.173 197.581 125.936C196.494 134.657 199.06 142.154 204.781 143.974C208.869 145.274 217.081 149.589 219.781 148.974C224.261 147.953 226.305 144.638 226.946 138.72C227.832 130.544 228.673 114.92 229.017 97.344C228.835 97.858 201.874 92.9 201.874 92.9Z"/>
                            <path @click="toggle(12)" :class="is(12)" class="tooth-path" d="M175.527 85.1C175.527 85.1 171.088 103.667 169.727 116.408C168.963 123.567 171.397 128.994 175.781 131.98C178.294 133.35 180.987 134.36 183.781 134.98C188.699 135.751 192.273 132.605 193.781 126.98C196.56 116.618 198.901 98.5 199.658 91.79C199.33 91.714 175.527 85.1 175.527 85.1Z"/>
                            <path @click="toggle(13)" :class="is(13)" class="tooth-path" d="M149.054 78.255C149.054 78.255 166.377 82.792 173.297 84.978C173.575 84.902 169.579 106.697 164.781 115.978C164.095 117.45 162.931 118.646 161.478 119.372C160.026 120.099 158.37 120.312 156.781 119.978C154.635 119.3 152.61 118.288 150.781 116.978C149.222 115.624 145.838 112.305 145.781 107.978C145.634 96.76 148.835 79.658 149.054 78.255Z"/>
                            <path @click="toggle(14)" :class="is(14)" class="tooth-path" d="M120.014 65.989C120.014 65.989 117.443 84.399 120.781 95.983C121.959 99.1773 124.044 101.958 126.781 103.983C129.262 105.66 131.951 107.005 134.781 107.983C138.411 109.496 139.83 108.345 142.389 102.167C144.378 97.367 145.853 86.918 146.808 76.135C146.427 76.383 120.014 65.989 120.014 65.989Z"/>

                            {{-- ── Lower teeth 15–28 ── --}}
                            <path @click="toggle(15)" :class="is(15)" class="tooth-path" d="M619.452 194.354C614.164 198.954 601.201 217.084 592.215 218.314C592.202 218.669 590.231 182.488 590.791 175.67C591.158 170.58 594.94 164.331 599.644 161.478C604.38 158.642 613.235 158.13 614.361 172.968C615.487 187.806 618.776 192.454 619.452 194.354Z"/>
                            <path @click="toggle(16)" :class="is(16)" class="tooth-path" d="M580.685 171.436C585.105 170.193 588.649 175.51 588.893 182.988C589.041 190.451 589.972 221.358 589.952 221.311C580.338 222.8 570.809 224.243 561.365 225.639C561.25 225.671 559.25 191.156 560.391 185.777C560.95 181.311 565.251 176.544 570.411 174.995C575.587 173.455 577.186 172.335 580.685 171.436Z"/>
                            <path @click="toggle(17)" :class="is(17)" class="tooth-path" d="M531.93 235.631C531.915 235.641 529.698 200.959 530.691 195.779C531.182 191.373 535.446 186.99 540.683 185.979C545.92 184.968 546.589 184.127 550.164 183.655C554.714 183.014 558.052 188.006 558.124 194.82C557.975 201.311 559.13 231.353 559.134 231.485C550.031 232.88 540.964 234.263 531.934 235.634Z"/>
                            <path @click="toggle(18)" :class="is(18)" class="tooth-path" d="M498.45 243.569C498.541 243.636 496.633 208.415 497.376 203.026C497.804 198.508 501.932 194.109 507.126 193.297C512.32 192.485 516.897 191.697 520.46 191.377C525.001 190.977 528.373 196.05 528.488 202.789C528.44 209.163 529.635 239.325 529.571 239.472C519.191 240.844 508.817 242.211 498.45 243.572Z"/>
                            <path @click="toggle(19)" :class="is(19)" class="tooth-path" d="M463.05 249.912C463.186 249.697 462.393 213.335 462.943 209.089C463.222 206.682 464.284 204.432 465.965 202.687C467.647 200.942 469.855 199.797 472.25 199.428C477.403 198.647 483.013 197.298 487.141 198.255C491.549 199.267 495.195 206.546 495.398 213.527C495.574 220.303 496.369 244.649 496.286 244.717C485.229 246.445 474.15 248.177 463.05 249.912Z"/>
                            <path @click="toggle(20)" :class="is(20)" class="tooth-path" d="M425.446 257.243C425.558 256.943 425.332 216.334 425.867 211.996C426.442 207.233 430.954 204.931 435.55 203.418C440.942 201.866 446.642 201.729 452.103 203.018C457.517 204.597 460.13 209.769 460.223 216.867C460.347 223.783 460.011 251.857 459.918 251.944C448.451 253.682 436.961 255.449 425.446 257.244Z"/>
                            <path @click="toggle(21)" :class="is(21)" class="tooth-path" d="M381.22 261.95C381.281 261.35 381.02 218.311 381.651 213.955C382.357 208.875 386.506 205.443 392.29 205.701C399.026 206.008 407.253 206.375 412.106 206.555C420.106 206.855 423.071 213.355 423.03 220.963C423.041 228.318 422.288 258.284 422.207 258.446C408.555 259.562 394.893 260.73 381.22 261.95Z"/>
                            <path @click="toggle(22)" :class="is(22)" class="tooth-path" d="M378.218 261.95C378.158 261.35 378.418 218.35 377.788 213.999C377.082 208.924 372.936 205.499 367.153 205.753C360.42 206.059 352.196 206.426 347.345 206.606C339.345 206.906 336.385 213.397 336.426 221C336.415 228.349 337.167 258.287 337.248 258.45C350.895 259.564 364.551 260.731 378.218 261.95Z"/>
                            <path @click="toggle(23)" :class="is(23)" class="tooth-path" d="M334.01 257.249C333.898 256.949 334.124 216.376 333.59 212.042C333.015 207.283 328.504 204.983 323.91 203.471C318.52 201.921 312.822 201.783 307.364 203.071C301.952 204.649 299.34 209.816 299.247 216.907C299.123 223.817 299.459 251.867 299.547 251.954C311.007 253.691 322.493 255.454 334.006 257.245Z"/>
                            <path @click="toggle(24)" :class="is(24)" class="tooth-path" d="M296.422 249.923C296.285 249.708 297.078 213.38 296.528 209.137C296.248 206.732 295.187 204.485 293.507 202.742C291.827 200.998 289.621 199.855 287.228 199.486C282.076 198.705 276.469 197.357 272.343 198.313C267.936 199.325 264.292 206.597 264.088 213.571C263.913 220.341 263.118 244.665 263.201 244.733C274.254 246.459 285.328 248.189 296.425 249.923Z"/>
                            <path @click="toggle(25)" :class="is(25)" class="tooth-path" d="M261.035 243.586C260.944 243.653 262.851 208.464 262.109 203.08C261.681 198.567 257.554 194.171 252.362 193.36C247.17 192.549 242.595 191.76 239.034 191.442C234.494 191.042 231.123 196.111 231.009 202.842C231.057 209.209 229.863 239.342 229.926 239.49C240.302 240.861 250.672 242.226 261.035 243.584Z"/>
                            <path @click="toggle(26)" :class="is(26)" class="tooth-path" d="M227.568 235.655C227.583 235.665 229.799 201.015 228.807 195.84C228.315 191.44 224.053 187.059 218.818 186.04C213.583 185.021 212.918 184.19 209.341 183.719C204.794 183.079 201.456 188.066 201.384 194.874C201.533 201.359 200.384 231.374 200.374 231.505C209.475 232.898 218.539 234.279 227.568 235.65Z"/>
                            <path @click="toggle(27)" :class="is(27)" class="tooth-path" d="M198.144 225.673C198.26 225.704 200.259 191.221 199.118 185.847C198.559 181.385 194.26 176.622 189.102 175.075C183.928 173.536 182.33 172.417 178.832 171.52C174.414 170.277 170.871 175.59 170.632 183.061C170.484 190.516 169.553 221.395 169.573 221.349C179.183 222.836 188.708 224.278 198.148 225.673Z"/>
                            <path @click="toggle(28)" :class="is(28)" class="tooth-path" d="M140.081 194.4C144.841 198.979 158.325 217.128 167.307 218.357C167.32 218.712 169.29 182.564 168.731 175.757C168.363 170.67 164.583 164.428 159.88 161.577C155.147 158.744 146.295 158.232 145.17 173.056C144.045 187.88 140.986 193.044 140.081 194.4Z"/>

                            {{-- Outer gum border --}}
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  class="pointer-events-none"
                                  d="M378.247 302.863C378.247 302.863 378.681 301.795 326.339 302.863C273.997 303.931 216.2 283.051 191.379 263.2C166.558 243.349 127.479 182.966 107.379 143.377C87.279 103.788 43.331 56.06 39.431 51.4C35.531 46.74 36.471 41.8 41.319 42.962C46.167 44.124 75.019 53.932 101.719 53.932C128.419 53.932 143.468 32 247.062 32C338.923 32 368.815 56.471 368.815 56.471C368.815 56.471 379.926 62.655 388.829 56.471C388.829 56.471 425.336 31.998 510.502 31.998C614.034 32 629.07 53.937 655.756 53.937C682.442 53.937 711.277 44.125 716.122 42.966C720.967 41.807 721.906 46.748 718.008 51.405C714.11 56.062 670.177 103.8 650.1 143.391C630.023 182.982 590.963 243.377 566.155 263.226C541.347 283.075 483.586 303.958 431.276 302.89C378.966 301.822 379.4 302.89 379.4 302.89Z"
                                  fill="#DF3F3B" opacity="0.15"/>

                            {{-- Number labels --}}
                            <g class="pointer-events-none select-none font-bold fill-slate-400 dark:fill-slate-500"
                               style="font-size:11px;font-family:sans-serif;">
                                <text x="628" y="58">۱</text>  <text x="604" y="70">۲</text>
                                <text x="568" y="78">۳</text>  <text x="536" y="84">۴</text>
                                <text x="503" y="88">۵</text>  <text x="466" y="92">۶</text>
                                <text x="418" y="94">۷</text>  <text x="337" y="94">۸</text>
                                <text x="290" y="92">۹</text>  <text x="250" y="88">۱۰</text>
                                <text x="215" y="84">۱۱</text> <text x="183" y="78">۱۲</text>
                                <text x="152" y="70">۱۳</text> <text x="122" y="58">۱۴</text>
                                <text x="614" y="230">۱۵</text><text x="574" y="240">۱۶</text>
                                <text x="540" y="250">۱۷</text><text x="506" y="258">۱۸</text>
                                <text x="472" y="264">۱۹</text><text x="438" y="268">۲۰</text>
                                <text x="398" y="272">۲۱</text><text x="355" y="272">۲۲</text>
                                <text x="315" y="268">۲۳</text><text x="278" y="264">۲۴</text>
                                <text x="242" y="258">۲۵</text><text x="205" y="250">۲۶</text>
                                <text x="170" y="240">۲۷</text><text x="130" y="230">۲۸</text>
                            </g>
                        </svg>
                    </div>

                    {{-- Selected teeth chips --}}
                    <div class="px-5 py-3 flex flex-wrap gap-1.5 min-h-[48px] border-t border-gray-50
                                dark:border-gray-700/50 bg-gray-50/60 dark:bg-gray-900/20">
                        <template x-for="tooth in selectedTeeth" :key="tooth">
                            <button @click="toggle(tooth)"
                                    class="w-8 h-8 flex items-center justify-center bg-indigo-600
                                           hover:bg-rose-500 text-white rounded-lg text-xs font-bold
                                           shadow-md shadow-indigo-200/50 dark:shadow-indigo-900/30 transition-all"
                                    x-text="tooth">
                            </button>
                        </template>
                        <template x-if="selectedTeeth.length === 0">
                            <span class="text-xs text-gray-400 dark:text-gray-500 self-center">
                                روی دندان کلیک کنید تا انتخاب شود
                            </span>
                        </template>
                    </div>
                </div>

                {{-- ══ ADD-TO-PLAN STRIP (visible when a service is selected) ══ --}}
                <div x-show="selectedService !== null"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-2"
                     class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-indigo-200
                            dark:border-indigo-700/50 shadow-lg shadow-indigo-100/40
                            dark:shadow-indigo-900/20 overflow-hidden">

                    {{-- Strip header --}}
                    <div class="px-5 py-3 flex items-center justify-between border-b border-indigo-100
                                dark:border-indigo-800/40"
                         style="background:linear-gradient(90deg,rgba(99,102,241,.07),transparent 70%);">
                        <h3 class="font-semibold text-indigo-700 dark:text-indigo-300 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            افزودن به طرح درمان
                        </h3>
                        <button @click="selectedService = null; selectedBrand = null;"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400
                                       hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Controls row --}}
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">

                        {{-- 1. Service name --}}
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">سرویس</label>
                            <div class="px-3 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/20
                                        border border-indigo-100 dark:border-indigo-800/30">
                                <p class="text-sm font-bold text-indigo-800 dark:text-indigo-200 truncate"
                                   x-text="selectedService?.name ?? '—'"></p>
                                <p class="text-[10px] text-indigo-500 dark:text-indigo-400 mt-0.5 truncate"
                                   x-text="selectedService?.category_name ?? ''"></p>
                            </div>
                        </div>

                        {{-- 2. Brand / tab selector --}}
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">برند / مواد</label>
                            <template x-if="selectedService?.custom_prices?.tabs?.length > 0">
                                <div class="space-y-1.5">
                                    {{-- Tab pills --}}
                                    <div class="flex gap-1 overflow-x-auto sc-thin pb-0.5">
                                        <template x-for="(tab, idx) in (selectedService?.custom_prices?.tabs ?? [])" :key="idx">
                                            <button @click="activePriceTab = idx; selectedBrand = null; pendingPrice = selectedService.base_price;"
                                                    :class="activePriceTab === idx
                                                        ? 'bg-amber-500 text-white shadow-sm shadow-amber-200/50'
                                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'"
                                                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold whitespace-nowrap transition-all shrink-0"
                                                    x-text="tab.title || 'تب'">
                                            </button>
                                        </template>
                                    </div>
                                    {{-- Brand select --}}
                                    <select @change="selectBrandByIndex($event.target.value)"
                                            class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-600
                                                   bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-100
                                                   focus:outline-none focus:border-indigo-400 focus:ring-2
                                                   focus:ring-indigo-100 dark:focus:ring-indigo-900/30">
                                        <option value="">— انتخاب برند —</option>
                                        <template x-for="(brand, bIdx) in (selectedService?.custom_prices?.tabs?.[activePriceTab]?.brands ?? [])" :key="bIdx">
                                            <option :value="bIdx"
                                                    x-text="brand.name + ' — ' + Number(brand.price).toLocaleString('fa-IR') + ' ت'">
                                            </option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <template x-if="!(selectedService?.custom_prices?.tabs?.length > 0)">
                                <div class="px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50
                                            border border-gray-200 dark:border-gray-600 text-sm text-gray-400">
                                    قیمت پایه
                                </div>
                            </template>
                        </div>

                        {{-- 3. Price + Qty --}}
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">قیمت (ت)</label>
                                <input x-model.number="pendingPrice" type="number" min="0"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-600
                                              bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-100
                                              focus:outline-none focus:border-indigo-400 focus:ring-2
                                              focus:ring-indigo-100 dark:focus:ring-indigo-900/30 text-left ltr"/>
                            </div>
                            <div class="w-[4.5rem]">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">تعداد</label>
                                <input x-model.number="pendingQty" type="number" min="1"
                                       class="w-full px-2 py-2 rounded-xl border border-gray-200 dark:border-gray-600
                                              bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-100
                                              focus:outline-none focus:border-indigo-400 focus:ring-2
                                              focus:ring-indigo-100 dark:focus:ring-indigo-900/30 text-center"/>
                            </div>
                        </div>

                        {{-- 4. Add button --}}
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">
                                مجموع: <span x-text="formatPrice(pendingPrice * pendingQty)"></span> ت
                            </label>
                            <button @click="addToPlan()"
                                    :disabled="!canAdd"
                                    :class="!canAdd ? 'opacity-50 cursor-not-allowed'
                                        : 'hover:shadow-xl hover:shadow-indigo-200/50 dark:hover:shadow-indigo-900/30 hover:scale-[1.02]'"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5
                                           rounded-xl text-white font-bold text-sm transition-all"
                                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                افزودن
                            </button>
                        </div>
                    </div>

                    {{-- Selected teeth context bar --}}
                    <div class="px-4 pb-3">
                        <div x-show="selectedTeeth.length > 0" class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold text-gray-400 uppercase shrink-0">دندان‌ها:</span>
                            <template x-for="t in selectedTeeth" :key="t">
                                <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/30
                                             text-indigo-700 dark:text-indigo-300 text-xs font-bold rounded"
                                      x-text="t"></span>
                            </template>
                        </div>
                        <div x-show="selectedTeeth.length === 0"
                             class="flex items-center gap-1.5 text-[11px] text-amber-600 dark:text-amber-400">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            هیچ دندانی انتخاب نشده — این مورد به صورت عمومی ثبت می‌شود
                        </div>
                    </div>
                </div>

            </div>{{-- /left col --}}

            {{-- ─────────────────────────────────────────
                 RIGHT COL (1/3): Service Picker
                 ───────────────────────────────────────── --}}
            <div class="xl:col-span-1 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200
                        dark:border-gray-700 shadow-sm overflow-hidden flex flex-col sticky top-4">

                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 space-y-3">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <span class="w-2 h-5 rounded-full bg-indigo-500 shrink-0"></span>
                        انتخاب سرویس
                    </h2>
                    <input x-model="serviceSearch" type="text" placeholder="جستجوی سرویس…"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                                  bg-gray-50 dark:bg-gray-700/50 text-sm text-gray-800 dark:text-gray-100
                                  placeholder-gray-400 focus:outline-none focus:border-indigo-400
                                  focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all"/>
                </div>

                {{-- Category pills --}}
                <div class="px-4 py-2.5 flex gap-2 overflow-x-auto sc-thin border-b border-gray-50 dark:border-gray-700/50">
                    <button @click="filterCategory = null"
                            :class="filterCategory === null
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                            class="px-3 py-1 rounded-lg text-xs font-bold whitespace-nowrap transition-all shrink-0">
                        همه
                    </button>
                    @foreach($categories as $cat)
                        <button @click="filterCategory = {{ $cat->id }}"
                                :class="filterCategory === {{ $cat->id }}
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                class="px-3 py-1 rounded-lg text-xs font-bold whitespace-nowrap transition-all shrink-0">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>

                {{-- Service list --}}
                <div class="flex-1 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-700/50 sc-thin"
                     style="max-height:460px;">
                    <template x-for="service in filteredServices" :key="service.id">
                        <div @click="selectService(service)"
                             :class="selectedService && selectedService.id === service.id
                                 ? 'svc-active'
                                 : 'hover:bg-gray-50 dark:hover:bg-gray-700/30'"
                             class="px-5 py-3.5 cursor-pointer transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0 flex items-center gap-2.5">
                                    {{-- Radio dot --}}
                                    <div :class="selectedService && selectedService.id === service.id
                                             ? 'bg-indigo-600 shadow-md shadow-indigo-200/50'
                                             : 'bg-gray-200 dark:bg-gray-600'"
                                         class="w-4 h-4 rounded-full shrink-0 flex items-center justify-center transition-all">
                                        <svg x-show="selectedService && selectedService.id === service.id"
                                             class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate"
                                           x-text="service.name"></p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5"
                                           x-text="service.category_name || '—'"></p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-left">
                                    <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400"
                                          x-text="Number(service.base_price).toLocaleString('fa-IR')"></span>
                                    <span class="text-[10px] text-gray-400 block">تومان</span>
                                </div>
                            </div>
                            {{-- Price tabs badge --}}
                            <div x-show="service.custom_prices?.tabs?.length > 0"
                                 class="flex items-center gap-1 mt-1.5 mr-[26px]">
                                <svg class="w-3 h-3 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-medium"
                                      x-text="`${service.custom_prices.tabs.length} تب قیمت`"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="filteredServices.length === 0">
                        <div class="p-10 text-center text-sm text-gray-400 dark:text-gray-500">سرویسی پیدا نشد</div>
                    </template>
                </div>
            </div>

        </div>{{-- /main grid --}}

        {{-- ══════════════════ PLAN ITEMS TABLE ══════════════════ --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <span class="w-2 h-5 rounded-full bg-violet-500 shrink-0"></span>
                    آیتم‌های طرح درمان
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full
                                 bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400
                                 text-xs font-bold"
                          x-text="planItems.length"></span>
                </h2>
                <button x-show="planItems.length > 0"
                        @click="planItems = []"
                        class="text-xs text-rose-500 hover:text-rose-700 font-medium flex items-center gap-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    پاک کردن همه
                </button>
            </div>

            {{-- Empty state --}}
            <div x-show="planItems.length === 0" class="py-16 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-400 dark:text-gray-500">طرح درمان خالی است</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">یک سرویس انتخاب کنید سپس به طرح اضافه کنید</p>
                <div class="flex items-center justify-center gap-5 mt-6 text-xs text-gray-400 dark:text-gray-500 flex-wrap">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-rose-100 dark:bg-rose-900/20 flex items-center justify-center
                                    text-rose-500 font-black text-[10px]">۱</div>
                        <span>دندان انتخاب کنید</span>
                    </div>
                    <span class="text-gray-200 dark:text-gray-700">←</span>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/20 flex items-center justify-center
                                    text-indigo-500 font-black text-[10px]">۲</div>
                        <span>سرویس انتخاب کنید</span>
                    </div>
                    <span class="text-gray-200 dark:text-gray-700">←</span>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-violet-100 dark:bg-violet-900/20 flex items-center justify-center
                                    text-violet-500 font-black text-[10px]">۳</div>
                        <span>«افزودن» را بزنید</span>
                    </div>
                </div>
            </div>

            {{-- Plan table --}}
            <div x-show="planItems.length > 0" class="overflow-x-auto">
                <table class="min-w-full text-sm text-right whitespace-nowrap">
                    <thead class="bg-gray-50/80 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-400 dark:text-gray-500 text-xs w-10">#</th>
                        <th class="px-4 py-3 font-semibold text-gray-400 dark:text-gray-500 text-xs">دندان‌ها</th>
                        <th class="px-4 py-3 font-semibold text-gray-400 dark:text-gray-500 text-xs">سرویس</th>
                        <th class="px-4 py-3 font-semibold text-gray-400 dark:text-gray-500 text-xs">برند / تب</th>
                        <th class="px-4 py-3 font-semibold text-gray-400 dark:text-gray-500 text-xs">تعداد</th>
                        <th class="px-4 py-3 font-semibold text-gray-400 dark:text-gray-500 text-xs text-left">واحد</th>
                        <th class="px-4 py-3 font-semibold text-gray-400 dark:text-gray-500 text-xs text-left">جمع</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    <template x-for="(item, idx) in planItems" :key="item.id">
                        <tr class="plan-row group hover:bg-violet-50/30 dark:hover:bg-violet-900/10 transition-colors anim-fade-up">
                            <td class="px-4 py-3 text-gray-300 dark:text-gray-600 text-xs font-mono"
                                x-text="idx + 1"></td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1 max-w-[140px]">
                                    <template x-for="t in item.teeth" :key="t">
                                        <span class="px-1.5 py-0.5 bg-indigo-100 dark:bg-indigo-900/30
                                                     text-indigo-700 dark:text-indigo-300
                                                     text-[10px] font-bold rounded"
                                              x-text="t"></span>
                                    </template>
                                    <span x-show="!item.teeth || item.teeth.length === 0"
                                          class="text-xs text-gray-400 italic">عمومی</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200"
                                x-text="item.service.name"></td>
                            <td class="px-4 py-3">
                                <span x-show="item.brand"
                                      class="text-xs font-medium text-amber-700 dark:text-amber-400"
                                      x-text="item.brand?.name ?? ''"></span>
                                <span x-show="item.tabTitle"
                                      class="text-[10px] text-gray-400 block"
                                      x-text="item.tabTitle ?? ''"></span>
                                <span x-show="!item.brand"
                                      class="text-xs text-gray-400">قیمت پایه</span>
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" min="1"
                                       :value="item.quantity"
                                       @change="updateQty(item.id, $event.target.value)"
                                       class="w-16 px-2 py-1.5 text-center rounded-lg border border-gray-200
                                              dark:border-gray-600 bg-white dark:bg-gray-700 text-sm
                                              text-gray-800 dark:text-gray-200 focus:outline-none
                                              focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100
                                              dark:focus:ring-indigo-900/30"/>
                            </td>
                            <td class="px-4 py-3 text-left">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300"
                                      x-text="formatPrice(item.price)"></span>
                                <span class="text-[10px] text-gray-400"> ت</span>
                            </td>
                            <td class="px-4 py-3 text-left">
                                <span class="text-sm font-bold text-violet-600 dark:text-violet-400"
                                      x-text="formatPrice(item.price * item.quantity)"></span>
                                <span class="text-[10px] text-violet-400"> ت</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button @click="removeItem(item.id)"
                                        class="row-del w-7 h-7 rounded-lg flex items-center justify-center
                                               text-gray-300 hover:text-rose-500 hover:bg-rose-50
                                               dark:hover:bg-rose-900/20 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ══════════════════ NOTES + SUMMARY ══════════════════ --}}
        <div x-show="planItems.length > 0"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- Notes --}}
            <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200
                        dark:border-gray-700 shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 mb-3">
                    <span class="w-2 h-5 rounded-full bg-teal-500 shrink-0"></span>
                    یادداشت‌ها
                </h3>
                <textarea x-model="notes" rows="5"
                          placeholder="توضیحات یا نکات مربوط به این طرح درمان…"
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600
                                 bg-gray-50 dark:bg-gray-700/50 text-sm text-gray-800 dark:text-gray-100
                                 placeholder-gray-400 focus:outline-none focus:border-teal-400
                                 focus:ring-2 focus:ring-teal-100 dark:focus:ring-teal-900/30
                                 resize-none transition-all">
                </textarea>
            </div>

            {{-- Financial summary --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200
                        dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">

                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <span class="w-2 h-5 rounded-full bg-emerald-500 shrink-0"></span>
                        خلاصه مالی
                    </h3>
                </div>

                <div class="p-5 space-y-4 flex-1">
                    {{-- Subtotal --}}
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">جمع کل موارد:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200"
                              x-text="formatPrice(subtotal) + ' تومان'"></span>
                    </div>

                    {{-- Discount row --}}
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">تخفیف</label>
                        <div class="flex items-center gap-2">
                            <select x-model="discountType"
                                    class="px-2 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600
                                           bg-white dark:bg-gray-700 text-xs text-gray-600 dark:text-gray-300
                                           focus:outline-none focus:border-rose-400 shrink-0">
                                <option value="amount">تومان</option>
                                <option value="percent">درصد</option>
                            </select>
                            <input x-model.number="discountAmount" type="number" min="0"
                                   placeholder="0"
                                   class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600
                                          bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200
                                          focus:outline-none focus:border-rose-400 focus:ring-2
                                          focus:ring-rose-50 dark:focus:ring-rose-900/30 text-left ltr"/>
                            <span class="text-xs text-rose-500 font-bold shrink-0"
                                  x-text="'−' + formatPrice(discountValue)"></span>
                        </div>
                    </div>

                    <div class="h-px bg-gray-100 dark:bg-gray-700"></div>

                    {{-- Grand total --}}
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-700 dark:text-gray-200">قابل پرداخت:</span>
                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400"
                              x-text="formatPrice(total)">
                        </span>
                    </div>
                    <p class="text-xs text-emerald-500 dark:text-emerald-600 text-left -mt-2">تومان</p>

                    {{-- Info chips --}}
                    <div class="grid grid-cols-3 gap-2">
                        <div class="px-2 py-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl text-center">
                            <div class="text-sm font-black text-indigo-600 dark:text-indigo-400"
                                 x-text="planItems.length"></div>
                            <div class="text-[10px] text-indigo-500 dark:text-indigo-400">سرویس</div>
                        </div>
                        <div class="px-2 py-2 bg-rose-50 dark:bg-rose-900/20 rounded-xl text-center">
                            <div class="text-sm font-black text-rose-600 dark:text-rose-400"
                                 x-text="uniqueTeethCount"></div>
                            <div class="text-[10px] text-rose-500 dark:text-rose-400">دندان</div>
                        </div>
                        <div class="px-2 py-2 bg-violet-50 dark:bg-violet-900/20 rounded-xl text-center">
                            <div class="text-sm font-black text-violet-600 dark:text-violet-400"
                                 x-text="totalQty"></div>
                            <div class="text-[10px] text-violet-500 dark:text-violet-400">تعداد</div>
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="px-5 pb-5 space-y-2.5">
                    <button :disabled="planItems.length === 0"
                            :class="planItems.length === 0 ? 'opacity-50 cursor-not-allowed'
                                : 'hover:shadow-xl hover:shadow-indigo-200/50 dark:hover:shadow-indigo-900/30 hover:scale-[1.01]'"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl
                                   text-white font-bold text-sm transition-all"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        تأیید و ذخیره طرح درمان
                    </button>
                    <button class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                                  border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300
                                  font-medium text-sm hover:border-indigo-300 hover:text-indigo-600
                                  dark:hover:text-indigo-400 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        ذخیره پیش‌نویس
                    </button>
                </div>
            </div>
        </div>

    </div>{{-- /x-data --}}

    <script>

        function treatmentPlanApp(services) {
            return {

                /* ── Dental chart ─────────────────────────────── */
                selectedTeeth : [],
                preset        : 'none',
                upperJawIds   : [1,2,3,4,5,6,7,8,9,10,11,12,13,14],
                lowerJawIds   : [15,16,17,18,19,20,21,22,23,24,25,26,27,28],

                toggle(id) {
                    if (this.selectedTeeth.includes(id)) {
                        this.selectedTeeth = this.selectedTeeth.filter(t => t !== id);
                    } else {
                        this.selectedTeeth.push(id);
                        this.selectedTeeth.sort((a, b) => a - b);
                    }
                    this.preset = 'none';
                },

                /** Returns the CSS class for a given tooth number */
                is(id) {
                    if (this.selectedTeeth.includes(id))               return 'tooth-path tooth-selected';
                    if (this.planItems.some(i => i.teeth.includes(id))) return 'tooth-path tooth-in-plan';
                    return 'tooth-path tooth-unselected';
                },

                selectJaw(type) {
                    if (this.preset === type) { this.resetTeeth(); return; }
                    this.preset        = type;
                    this.selectedTeeth = type === 'upper'
                        ? [...this.upperJawIds]
                        : [...this.lowerJawIds];
                },

                selectAllTeeth() {
                    if (this.preset === 'all') { this.resetTeeth(); return; }
                    this.preset        = 'all';
                    this.selectedTeeth = [...this.upperJawIds, ...this.lowerJawIds];
                },

                resetTeeth() {
                    this.selectedTeeth = [];
                    this.preset        = 'none';
                },

                /* ── Services ──────────────────────────────────── */
                services       : services,
                selectedService: null,
                serviceSearch  : '',
                filterCategory : null,
                activePriceTab : 0,
                selectedBrand  : null,
                pendingQty     : 1,
                pendingPrice   : 0,

                init() {
                    this.$watch('selectedService', val => {
                        if (val) {
                            this.selectedBrand  = null;
                            this.activePriceTab = 0;
                            this.pendingPrice   = val.base_price || 0;
                            this.pendingQty     = 1;
                        }
                    });
                },

                get filteredServices() {
                    const q = this.serviceSearch.toLowerCase();
                    return this.services.filter(s => {
                        const ms = !q || s.name.toLowerCase().includes(q);
                        const mc = this.filterCategory === null || s.category_id === this.filterCategory;
                        return ms && mc;
                    });
                },

                selectService(service) {
                    if (this.selectedService && this.selectedService.id === service.id) {
                        this.selectedService = null;
                        this.selectedBrand   = null;
                        return;
                    }
                    this.selectedService = service;
                },

                selectBrandByIndex(bIdx) {
                    if (bIdx === '' || bIdx == null) {
                        this.selectedBrand = null;
                        this.pendingPrice  = this.selectedService?.base_price || 0;
                        return;
                    }
                    const brands = this.selectedService?.custom_prices?.tabs?.[this.activePriceTab]?.brands ?? [];
                    const brand  = brands[parseInt(bIdx)];
                    if (brand) {
                        this.selectedBrand = brand;
                        this.pendingPrice  = brand.price;
                    }
                },

                /* ── Treatment plan ────────────────────────────── */
                planItems     : [],
                patientName   : '',
                notes         : '',
                discountAmount: 0,
                discountType  : 'amount',  // 'amount' | 'percent'

                get canAdd() {
                    return this.selectedService !== null;
                },

                addToPlan() {
                    if (!this.canAdd) return;
                    const tab = this.selectedService?.custom_prices?.tabs?.[this.activePriceTab] ?? null;
                    this.planItems.push({
                        id       : Date.now(),
                        teeth    : [...this.selectedTeeth],
                        service  : { id: this.selectedService.id, name: this.selectedService.name },
                        brand    : this.selectedBrand ? { name: this.selectedBrand.name } : null,
                        tabTitle : tab?.title ?? null,
                        price    : Number(this.pendingPrice) || 0,
                        quantity : Math.max(1, Number(this.pendingQty) || 1),
                    });
                    // Reset after adding
                    this.selectedTeeth  = [];
                    this.preset         = 'none';
                    this.selectedService = null;
                    this.selectedBrand  = null;
                    this.pendingQty     = 1;
                    this.pendingPrice   = 0;
                },

                removeItem(id) {
                    this.planItems = this.planItems.filter(i => i.id !== id);
                },

                updateQty(id, qty) {
                    const item = this.planItems.find(i => i.id === id);
                    if (item) item.quantity = Math.max(1, Number(qty) || 1);
                },

                /* ── Computed totals ───────────────────────────── */
                get subtotal() {
                    return this.planItems.reduce((s, i) => s + i.price * i.quantity, 0);
                },

                get discountValue() {
                    const d = Number(this.discountAmount) || 0;
                    if (this.discountType === 'percent')
                        return Math.min(this.subtotal, this.subtotal * d / 100);
                    return Math.min(this.subtotal, d);
                },

                get total() {
                    return Math.max(0, this.subtotal - this.discountValue);
                },

                get uniqueTeethCount() {
                    return new Set(this.planItems.flatMap(i => i.teeth)).size;
                },

                get totalQty() {
                    return this.planItems.reduce((s, i) => s + i.quantity, 0);
                },

                formatPrice(n) {
                    return Number(n).toLocaleString('fa-IR');
                },
            };
        }
    </script>
@endsection
