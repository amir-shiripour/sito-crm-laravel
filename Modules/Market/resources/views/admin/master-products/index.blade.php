@extends('layouts.user')

@php($title = 'کاتالوگ محصولات (Master)')

@section('content')
    <div class="space-y-4">
        {{-- هدر --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">مدیریت کاتالوگ محصولات</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">محصولات مرجع که فروشندگان می‌توانند روی آن‌ها قیمت‌گذاری کنند.</p>
            </div>
            <div>
                @can('market.manage')
                    <a href="{{ route('user.market.master-products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        ثبت محصول جدید
                    </a>
                @endcan
            </div>
        </div>

        {{-- جدول --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap text-sm text-right">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">کد هوشمند (CRM)</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">نام محصول</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">برند و دسته</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">وضعیت</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 dark:text-gray-300 text-left pl-6">عملیات</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="px-4 py-3 font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ $product->crm_code }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $product->title }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <span class="block text-gray-800 dark:text-gray-300">{{ optional($product->brand)->name ?? '-' }}</span>
                                <span>{{ optional($product->category)->name ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($product->status === 'active')
                                    <span class="px-2 py-1 rounded-md bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400 text-xs">فعال</span>
                                @else
                                    <span class="px-2 py-1 rounded-md bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 text-xs">پیشنویس</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-left">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('user.market.master-products.edit', $product) }}" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                    <form action="{{ route('user.market.master-products.destroy', $product) }}" method="POST" onsubmit="return confirm('حذف شود؟');" class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-500">هیچ محصولی در کاتالوگ یافت نشد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages()) <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">{{ $products->links() }}</div> @endif
        </div>
    </div>
@endsection
