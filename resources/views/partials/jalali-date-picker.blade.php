{{-- resources/views/partials/jalali-date-picker.blade.php --}}
@once
    <style>
        /* ---------- حالت پایه (روشن) ---------- */

        jdp-container {
            background: #ffffff;
            color: #111827; /* gray-900 */
            border: 1px solid #e5e7eb; /* gray-200 */
            box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.15),
            0 4px 6px -4px rgba(15, 23, 42, 0.12);
        }

        jdp-container .jdp-day,
        jdp-container .jdp-day-name {
            color: #111827; /* gray-900 */
        }

        /* روزهای هدر هفته */
        jdp-container .jdp-day-name {
            background-color: #f3f4f6; /* gray-100 */
        }

        /* روز امروز */
        jdp-container .jdp-day.today,
        jdp-container .jdp-day-name.today {
            border-color: rgba(99, 102, 241, 0.5); /* indigo-ish */
        }

        /* روز/هدر انتخاب شده */
        jdp-container .jdp-day.selected,
        jdp-container .jdp-day-name.selected {
            background-color: #4f46e5 !important; /* indigo-600 */
            color: #ffffff !important;
        }

        /* روزهای غیرفعال و خارج از ماه */
        jdp-container .jdp-day.not-in-month {
            opacity: 0.35;
            color: #9ca3af; /* gray-400 */
        }

        jdp-container .jdp-day.disabled-day {
            opacity: 0.2;
        }

        /* hover روی روز قابل انتخاب */
        jdp-container .jdp-day:not(.disabled-day):hover {
            background: rgba(79, 70, 229, 0.08); /* indigo-600 با شفافیت */
            transform: scale(1.1);
        }

        /* دکمه‌های پایین (امروز، خالی، بستن) */
        jdp-container .jdp-btn-close,
        jdp-container .jdp-btn-empty,
        jdp-container .jdp-btn-today {
            background: #4f46e5; /* indigo-600 */
            color: #ffffff;
            border-radius: .5rem;
            padding: .35em .9em;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }

        jdp-container .jdp-btn-close:hover,
        jdp-container .jdp-btn-empty:hover,
        jdp-container .jdp-btn-today:hover {
            background: #4338ca; /* indigo-700 */
        }

        /* تایم‌پیکر (ساعت/دقیقه) */
        jdp-container .jdp-time-container .jdp-time select {
            background: #f9fafb; /* gray-50 */
            border-radius: .75rem;
            border: 1px solid #e5e7eb;
        }

        jdp-container .jdp-time-container.jdp-only-time .jdp-time select {
            background: #f9fafb;
        }

        /* ناوبری سال/ماه (+ / -) */
        jdp-container .jdp-icon-plus,
        jdp-container .jdp-icon-minus {
            border-radius: .5rem;
            border-color: #e5e7eb;
            background-color: #f9fafb;
        }

        jdp-container .jdp-icon-plus:hover,
        jdp-container .jdp-icon-minus:hover {
            background-color: #e5e7eb;
        }

        /* ---------- حالت تاریک (dark mode) ---------- */

        /* اوورلی روی موبایل */
        .dark jdp-overlay {
            background-color: rgba(15, 23, 42, 0.65); /* slate-900 با شفافیت */
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        .dark jdp-container {
            background: #020617; /* slate-950 */
            color: #e5e7eb; /* gray-200 */
            border-color: #1f2937; /* gray-800 */
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7),
            0 0 0 1px rgba(15, 23, 42, 0.9);
        }

        .dark jdp-container .jdp-day,
        .dark jdp-container .jdp-day-name {
            color: #e5e7eb; /* gray-200 */
        }

        .dark jdp-container .jdp-day-name {
            background-color: #111827; /* gray-900 */
        }

        .dark jdp-container .jdp-day.today,
        .dark jdp-container .jdp-day-name.today {
            border-color: rgba(129, 140, 248, 0.7); /* indigo-400/500 */
        }

        .dark jdp-container .jdp-day.selected,
        .dark jdp-container .jdp-day-name.selected {
            background-color: #6366f1 !important; /* indigo-500 */
            color: #f9fafb !important;
        }

        .dark jdp-container .jdp-day.not-in-month {
            color: #4b5563; /* gray-600 */
            opacity: 0.4;
        }

        .dark jdp-container .jdp-day.disabled-day {
            opacity: 0.16;
        }

        .dark jdp-container .jdp-day:not(.disabled-day):hover {
            background: rgba(129, 140, 248, 0.25);
            transform: scale(1.1);
        }

        /* دکمه‌های پایین در دارک */
        .dark jdp-container .jdp-btn-close,
        .dark jdp-container .jdp-btn-empty,
        .dark jdp-container .jdp-btn-today {
            background: #4f46e5; /* indigo-600 */
            color: #e5e7eb;
            box-shadow: 0 10px 30px rgba(88, 80, 236, 0.65);
        }

        .dark jdp-container .jdp-btn-close:hover,
        .dark jdp-container .jdp-btn-empty:hover,
        .dark jdp-container .jdp-btn-today:hover {
            background: #4338ca; /* indigo-700 */
        }

        /* آیکون‌های + و - در دارک */
        .dark jdp-container .jdp-icon-plus,
        .dark jdp-container .jdp-icon-minus {
            border-color: #374151; /* gray-700 */
            background-color: #020617; /* slate-950 */
        }

        .dark jdp-container .jdp-icon-plus svg,
        .dark jdp-container .jdp-icon-minus svg {
            fill: #e5e7eb;
        }

        /* ورودی‌ها و سلکت‌های هدر (سال/ماه/ساعت/دقیقه) در دارک */
        .dark jdp-container .jdp-month input,
        .dark jdp-container .jdp-month select,
        .dark jdp-container .jdp-year input,
        .dark jdp-container .jdp-year select,
        .dark jdp-container .jdp-time input,
        .dark jdp-container .jdp-time select {
            background-color: #020617; /* slate-950 */
            color: #f9fafb;
        }

        .dark jdp-container .jdp-time-container .jdp-time select {
            background-color: #020617;
            border-color: #374151; /* gray-700 */
        }

        /* فوکوس ورودی‌های دارک */
        .dark jdp-container .jdp-month input:focus,
        .dark jdp-container .jdp-month select:focus,
        .dark jdp-container .jdp-year input:focus,
        .dark jdp-container .jdp-year select:focus,
        .dark jdp-container .jdp-time input:focus,
        .dark jdp-container .jdp-time select:focus {
            outline: none;
            box-shadow: 0 0 0 1px #4f46e5;
        }

        /* فوتر (برای زمانی که چیزی بهش اضافه شد) */
        .dark jdp-container .jdp-footer {
            background-color: #020617;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jalaliDatepicker) {

                // 📅 تمام فیلدهای تاریخ معمولی
                jalaliDatepicker.startWatch({
                    selector: '[data-jdp]',
                    // time:true,
                });

                jalaliDatepicker.startWatch({
                    selector: '[data-jdp-only-date]'
                });

                // ⏰ تمام فیلدهای فقط زمان
                // اگر ورژن‌تون timeOnly رو ساپورت نکنه، time:true کار می‌کنه و
                // فقط ممکنه یک پاپ‌آپ تاریخ هم همراهش باشه که بعداً می‌تونیم ریزتر تنظیمش کنیم.
                jalaliDatepicker.startWatch({
                    selector: '[data-jdp-only-time]',
                    hasSecond: false,
                });
                jalaliDatepicker.startWatch({
                    selector: '[data-jdp-with-time]',
                    time: true,
                    hasSecond: false,
                });
            }
        });
    </script>
@endonce
