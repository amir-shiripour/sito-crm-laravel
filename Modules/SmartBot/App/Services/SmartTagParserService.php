<?php

declare(strict_types=1);

namespace Modules\SmartBot\App\Services;

final class SmartTagParserService
{
    /**
     * Parse smart tags inside response text and replace them with interactive HTML widgets.
     */
    public static function parse(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // First escape HTML to prevent XSS in standard text
        $html = e($text);

        // Convert newlines to <br> for plain text parts
        $html = nl2br($html);

        // 1. Parse [card ...] tags
        $html = preg_replace_callback('/\[card\s+([^\]]+)\]/i', function ($matches) {
            $attrs = self::parseAttributes($matches[1]);
            $number = e($attrs['number'] ?? '');
            $bank = e($attrs['bank'] ?? 'کارت بانکی');
            $owner = e($attrs['owner'] ?? '');

            if (empty($number)) {
                return '';
            }

            $cleanNumber = preg_replace('/[^0-9]/', '', $number);
            $formattedNumber = implode(' ', str_split($cleanNumber, 4));
            $ownerText = $owner ? 'به نام: ' . $owner : '';

            return <<<HTML
<div x-data="{ copied: false }" class="my-3 overflow-hidden p-4 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-850 to-emerald-950 text-white border border-slate-700/60 shadow-lg shadow-emerald-950/20 space-y-3 font-iranYekan not-prose dir-rtl text-right">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30 text-xs">
                💳
            </div>
            <span class="text-xs font-bold text-slate-200">{$bank}</span>
        </div>
        <span class="text-[11px] font-semibold text-slate-400">{$ownerText}</span>
    </div>
    <div class="flex items-center justify-between bg-black/40 backdrop-blur-md px-3.5 py-2.5 rounded-xl border border-white/10 dir-ltr" dir="ltr">
        <span class="text-sm sm:text-base font-bold tracking-widest text-emerald-300 dir-ltr" style="direction: ltr !important; text-align: left;">
            {$formattedNumber}
        </span>
        <button 
            type="button"
            @click="
                (function(val){
                    var p=['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], a=['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
                    for(var i=0;i<10;i++){ val=val.replace(new RegExp(p[i],'g'),i).replace(new RegExp(a[i],'g'),i); }
                    if(/[0-9]/.test(val) && /^[0-9,\s\u060C\u066Cتومانریالtomanrial]+$/i.test(val)){ val=val.replace(/[^0-9]/g,''); } else { val=val.trim(); }
                    if (navigator.clipboard && window.isSecureContext) {
                        return navigator.clipboard.writeText(val);
                    } else {
                        var el = document.createElement('textarea');
                        el.value = val;
                        el.style.position = 'fixed';
                        el.style.left = '-9999px';
                        document.body.appendChild(el);
                        el.focus();
                        el.select();
                        try { document.execCommand('copy'); } catch(e){}
                        document.body.removeChild(el);
                        return Promise.resolve();
                    }
                })('{$cleanNumber}').then(() => { copied = true; setTimeout(() => copied = false, 2000); });
            "
            class="px-3 py-1.5 text-[11px] font-bold rounded-lg transition-all flex items-center gap-1 cursor-pointer dir-rtl shrink-0 mr-2"
            :class="copied ? 'bg-emerald-500 text-white shadow-md' : 'bg-white/10 hover:bg-white/20 text-slate-200'"
        >
            <template x-if="!copied">
                <span>کپی کارت</span>
            </template>
            <template x-if="copied">
                <span>کپی شد ✓</span>
            </template>
        </button>
    </div>
</div>
HTML;
        }, $html);

        // 2. Parse [iban ...] tags
        $html = preg_replace_callback('/\[iban\s+([^\]]+)\]/i', function ($matches) {
            $attrs = self::parseAttributes($matches[1]);
            $rawCode = trim($attrs['code'] ?? $attrs['iban'] ?? '');
            $bank = e($attrs['bank'] ?? 'شماره شبا');
            $owner = e($attrs['owner'] ?? '');

            if (empty($rawCode)) {
                return '';
            }

            $cleanCode = preg_replace('/^IR/i', '', str_replace(' ', '', $rawCode));
            $displayCode = 'IR' . $cleanCode;
            $ownerText = $owner ? 'به نام: ' . $owner : '';

            return <<<HTML
<div x-data="{ copied: false }" class="my-3 overflow-hidden p-4 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-850 to-sky-950 text-white border border-slate-700/60 shadow-lg shadow-sky-950/20 space-y-3 font-iranYekan not-prose dir-rtl text-right">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center border border-sky-500/30 text-xs">
                🏦
            </div>
            <span class="text-xs font-bold text-slate-200">{$bank}</span>
        </div>
        <span class="text-[11px] font-semibold text-slate-400">{$ownerText}</span>
    </div>
    <div class="flex items-center justify-between bg-black/40 backdrop-blur-md px-3.5 py-2.5 rounded-xl border border-white/10 dir-ltr" dir="ltr">
        <span class="text-xs sm:text-sm font-bold tracking-wider text-sky-300 truncate mr-2 dir-ltr" style="direction: ltr !important; text-align: left;">
            {$displayCode}
        </span>
        <button 
            type="button"
            @click="
                (function(val){
                    var p=['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], a=['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
                    for(var i=0;i<10;i++){ val=val.replace(new RegExp(p[i],'g'),i).replace(new RegExp(a[i],'g'),i); }
                    val = val.replace(/^ir/i, '').replace(/[^0-9]/g, '').trim();
                    if (navigator.clipboard && window.isSecureContext) {
                        return navigator.clipboard.writeText(val);
                    } else {
                        var el = document.createElement('textarea');
                        el.value = val;
                        el.style.position = 'fixed';
                        el.style.left = '-9999px';
                        document.body.appendChild(el);
                        el.focus();
                        el.select();
                        try { document.execCommand('copy'); } catch(e){}
                        document.body.removeChild(el);
                        return Promise.resolve();
                    }
                })('{$cleanCode}').then(() => { copied = true; setTimeout(() => copied = false, 2000); });
            "
            class="shrink-0 px-3 py-1.5 text-[11px] font-bold rounded-lg transition-all flex items-center gap-1 cursor-pointer dir-rtl"
            :class="copied ? 'bg-sky-500 text-white shadow-md' : 'bg-white/10 hover:bg-white/20 text-slate-200'"
        >
            <template x-if="!copied">
                <span>کپی شبا</span>
            </template>
            <template x-if="copied">
                <span>کپی شد ✓</span>
            </template>
        </button>
    </div>
</div>
HTML;
        }, $html);

        // 3. Parse [crypto ...] tags
        $html = preg_replace_callback('/\[crypto\s+([^\]]+)\]/i', function ($matches) {
            $attrs = self::parseAttributes($matches[1]);
            $currency = e(strtoupper($attrs['currency'] ?? $attrs['coin'] ?? 'Crypto'));
            $network = e(strtoupper($attrs['network'] ?? 'TRC20'));
            $address = e($attrs['address'] ?? $attrs['wallet'] ?? '');

            if (empty($address)) {
                return '';
            }

            return <<<HTML
<div x-data="{ copied: false }" class="my-3 overflow-hidden p-4 rounded-2xl bg-gradient-to-br from-slate-900 via-purple-950 to-slate-900 text-white border border-purple-900/50 shadow-lg shadow-purple-950/20 space-y-3 font-iranYekan not-prose dir-rtl text-right">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-purple-500/20 text-purple-300 flex items-center justify-center border border-purple-500/30 text-xs">
                🪙
            </div>
            <span class="text-xs font-bold text-slate-200">کیف پول {$currency}</span>
        </div>
        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-purple-500/20 text-purple-300 border border-purple-500/30 uppercase">
            {$network}
        </span>
    </div>
    <div class="flex items-center justify-between bg-black/40 backdrop-blur-md px-3.5 py-2.5 rounded-xl border border-white/10 dir-ltr" dir="ltr">
        <span class="text-xs font-semibold tracking-wider text-purple-200 truncate mr-2 dir-ltr" style="direction: ltr !important; text-align: left;">
            {$address}
        </span>
        <button 
            type="button"
            @click="
                (function(val){
                    var p=['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], a=['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
                    for(var i=0;i<10;i++){ val=val.replace(new RegExp(p[i],'g'),i).replace(new RegExp(a[i],'g'),i); }
                    val = val.trim();
                    if (navigator.clipboard && window.isSecureContext) {
                        return navigator.clipboard.writeText(val);
                    } else {
                        var el = document.createElement('textarea');
                        el.value = val;
                        el.style.position = 'fixed';
                        el.style.left = '-9999px';
                        document.body.appendChild(el);
                        el.focus();
                        el.select();
                        try { document.execCommand('copy'); } catch(e){}
                        document.body.removeChild(el);
                        return Promise.resolve();
                    }
                })('{$address}').then(() => { copied = true; setTimeout(() => copied = false, 2000); });
            "
            class="shrink-0 px-3 py-1.5 text-[11px] font-bold rounded-lg transition-all flex items-center gap-1 cursor-pointer dir-rtl"
            :class="copied ? 'bg-purple-500 text-white shadow-md' : 'bg-white/10 hover:bg-white/20 text-slate-200'"
        >
            <template x-if="!copied">
                <span>کپی آدرس</span>
            </template>
            <template x-if="copied">
                <span>کپی شد ✓</span>
            </template>
        </button>
    </div>
</div>
HTML;
        }, $html);

        // 4. Parse [copy ...] tags (Copyable Code/Text block)
        $html = preg_replace_callback('/\[copy\s+([^\]]+)\]/i', function ($matches) {
            $attrs = self::parseAttributes($matches[1]);
            $text = e($attrs['text'] ?? $attrs['code'] ?? $attrs['value'] ?? $attrs['val'] ?? '');
            $label = e($attrs['label'] ?? $attrs['title'] ?? '');

            if (empty($text) && empty($label)) {
                return '';
            }

            $labelHtml = !empty($label) ? '<span class="font-bold text-slate-700 dark:text-slate-200">' . $label . ':</span>' : '';
            $textHtml = !empty($text) ? '<span class="font-bold text-indigo-600 dark:text-indigo-400 dir-ltr">' . $text . '</span>' : '';

            return <<<HTML
<span x-data="{ copied: false }" class="inline-flex max-w-full items-center gap-1.5 mx-1 my-1 px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 align-middle not-prose dir-rtl shadow-2xs">
    {$labelHtml}
    {$textHtml}
    <button 
        type="button"
        @click="
            (function(val){
                var p=['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], a=['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
                for(var i=0;i<10;i++){ val=val.replace(new RegExp(p[i],'g'),i).replace(new RegExp(a[i],'g'),i); }
                if(/[0-9]/.test(val) && /^[0-9,\s\u060C\u066Cتومانریالtomanrial]+$/i.test(val)){ val=val.replace(/[^0-9]/g,''); } else { val=val.trim(); }
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(val);
                } else {
                    var el = document.createElement('textarea');
                    el.value = val;
                    el.style.position = 'fixed';
                    el.style.left = '-9999px';
                    document.body.appendChild(el);
                    el.focus();
                    el.select();
                    try { document.execCommand('copy'); } catch(e){}
                    document.body.removeChild(el);
                    return Promise.resolve();
                }
            })('{$text}').then(() => { copied = true; setTimeout(() => copied = false, 2000); });
        "
        class="mr-0.5 px-2 py-0.5 text-[11px] font-bold rounded-lg transition-all cursor-pointer shrink-0"
        :class="copied ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-700 dark:text-slate-200'"
    >
        <template x-if="!copied">
            <span>کپی</span>
        </template>
        <template x-if="copied">
            <span>✓</span>
        </template>
    </button>
</span>
HTML;
        }, $html);

        // 5. Parse [phone ...] tags
        $html = preg_replace_callback('/\[phone\s+([^\]]+)\]/i', function ($matches) {
            $attrs = self::parseAttributes($matches[1]);
            $number = e($attrs['number'] ?? $attrs['phone'] ?? $attrs['text'] ?? '');
            $label = e($attrs['label'] ?? ($number ? 'تماس با ' . $number : 'تماس'));

            $cleanPhone = preg_replace('/[^0-9\+]/', '', $number);

            return <<<HTML
<a href="tel:{$cleanPhone}" class="inline-flex items-center gap-1.5 mx-1 my-1 px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/80 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition-all font-bold not-prose dir-rtl align-middle shadow-2xs">
    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.826-1.01-5.09-3.274-6.1-6.1l1.292-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
    <span>{$label}</span>
</a>
HTML;
        }, $html);

        // 6. Parse [button ...] tags
        $html = preg_replace_callback('/\[button\s+([^\]]+)\]/i', function ($matches) {
            $attrs = self::parseAttributes($matches[1]);
            $url = e($attrs['url'] ?? $attrs['link'] ?? $attrs['href'] ?? '#');
            $label = e($attrs['label'] ?? $attrs['text'] ?? 'مشاهده و اقدام');

            return <<<HTML
<a href="{$url}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-1.5 w-full sm:w-auto mx-0 sm:mx-1 my-1.5 px-4 py-2 sm:px-3 sm:py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/80 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-all font-bold not-prose dir-rtl align-middle shadow-2xs text-center">
    <span>{$label}</span>
    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
</a>
HTML;
        }, $html);

        // 7. Clean up extra <br> line breaks immediately preceding or following block widgets (cards/iban/crypto)
        $html = preg_replace('/(?:<br\s*\/?>\s*)+(<div\s+)/i', '$1', $html);
        $html = preg_replace('/(<\/div>)\s*(?:<br\s*\/?>\s*)+/i', '$1', $html);

        return $html;
    }

    /**
     * Parse attribute string like `number="6274..." bank="ملی" owner="علی"`
     */
    private static function parseAttributes(string $attrString): array
    {
        $attributes = [];
        $pattern = '/(\w+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|(\S+))/u';

        if (preg_match_all($pattern, html_entity_decode($attrString, ENT_QUOTES | ENT_HTML5), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $key = strtolower($match[1]);
                $val = '';
                for ($i = 2; $i < count($match); $i++) {
                    if (isset($match[$i]) && $match[$i] !== '') {
                        $val = $match[$i];
                        break;
                    }
                }
                $attributes[$key] = trim($val);
            }
        }

        return $attributes;
    }
}
