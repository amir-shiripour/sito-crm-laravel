{{-- Search Input --}}
<div class="col-span-1 sm:col-span-2 lg:col-span-1">
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">جستجو</label>
    <div class="relative">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="عنوان، کد، آدرس..."
               class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </div>
</div>

{{-- Listing Type --}}
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">نوع معامله</label>
    <div class="relative">
        <select name="listing_type" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
            <option value="">همه موارد</option>
            <option value="sale" {{ request('listing_type') == 'sale' ? 'selected' : '' }}>فروش</option>
            <option value="rent" {{ request('listing_type') == 'rent' ? 'selected' : '' }}>رهن و اجاره</option>
            <option value="presale" {{ request('listing_type') == 'presale' ? 'selected' : '' }}>پیش‌فروش</option>
        </select>
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
    </div>
</div>

{{-- Property Type --}}
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">نوع ملک</label>
    <div class="relative">
        <select name="property_type" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
            <option value="">همه موارد</option>
            <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>آپارتمان</option>
            <option value="villa" {{ request('property_type') == 'villa' ? 'selected' : '' }}>ویلا</option>
            <option value="land" {{ request('property_type') == 'land' ? 'selected' : '' }}>زمین</option>
            <option value="office" {{ request('property_type') == 'office' ? 'selected' : '' }}>اداری</option>
        </select>
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
        </div>
    </div>
</div>

{{-- Status --}}
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت</label>
    <div class="relative">
        <select name="status_id" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
            <option value="">همه وضعیت‌ها</option>
            @foreach($statuses as $status)
                <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>{{ $status->label ?? $status->name }}</option>
            @endforeach
        </select>
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
    </div>
</div>

{{-- Category --}}
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">دسته‌بندی</label>
    <div class="relative">
        <select name="category_id" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
            <option value="">همه دسته‌بندی‌ها</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
        </div>
    </div>
</div>

{{-- Building --}}
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">ساختمان</label>
    <div class="relative">
        <select name="building_id" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
            <option value="">همه ساختمان‌ها</option>
            @foreach($buildings as $building)
                <option value="{{ $building->id }}" {{ request('building_id') == $building->id ? 'selected' : '' }}>{{ $building->name }}</option>
            @endforeach
        </select>
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
        </div>
    </div>
</div>

{{-- Publication Status --}}
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">وضعیت انتشار</label>
    <div class="relative">
        <select name="publication_status" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
            <option value="">همه</option>
            <option value="published" {{ request('publication_status') == 'published' ? 'selected' : '' }}>منتشر شده</option>
            <option value="draft" {{ request('publication_status') == 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
        </select>
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
        </div>
    </div>
</div>

{{-- Agent (for admins) --}}
@if($canManageProperties && !empty($agents))
    <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">مشاور</label>
        <div class="relative">
            <select name="agent_id" class="w-full appearance-none pl-10 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white transition-all dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-800">
                <option value="">همه مشاوران</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                @endforeach
            </select>
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            </div>
        </div>
    </div>
@endif

{{-- Buttons and Show All toggle --}}
<div class="col-span-1 sm:col-span-2 lg:col-span-4 flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100 dark:border-gray-700 mt-2">
    @if(auth()->user()->hasRole('super-admin') || auth()->user()->can('properties.view.all') || auth()->user()->can('properties.manage'))
        <label class="inline-flex items-center cursor-pointer group">
            <div class="relative">
                <input type="checkbox" name="show_all" value="1" class="sr-only peer" {{ request('show_all') ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
            </div>
            <span class="mr-3 text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">نمایش همه املاک</span>
        </label>
    @else
        <div></div> {{-- Spacer --}}
    @endif

    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 dark:focus:ring-indigo-900 transition-all shadow-lg shadow-indigo-500/30 active:scale-95">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
        اعمال فیلتر
    </button>
</div>
