@props(['submit'])

<div {{ $attributes->merge(['class' => 'flex flex-col lg:flex-row gap-8']) }}>
    <div class="lg:w-1/3">
        <x-section-title>
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>
    </div>

    <div class="lg:w-2/3">
        <form wire:submit="{{ $submit }}" class="bg-gray-50/50 dark:bg-gray-900/30 rounded-2xl border border-gray-100 dark:border-gray-700/50 shadow-inner overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div class="flex items-center justify-end px-4 py-4 bg-gray-100/50 dark:bg-gray-900/50 text-end sm:px-6 border-t border-gray-200 dark:border-gray-700">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>
