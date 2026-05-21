<div {{ $attributes->merge(['class' => 'flex flex-col lg:flex-row gap-8']) }}>
    <div class="lg:w-1/3">
        <x-section-title>
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>
    </div>

    <div class="lg:w-2/3">
        <div class="px-4 py-5 sm:p-6 bg-gray-50/50 dark:bg-gray-900/30 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-inner">
            {{ $content }}
        </div>
    </div>
</div>
