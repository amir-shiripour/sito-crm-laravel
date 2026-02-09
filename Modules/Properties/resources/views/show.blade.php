@extends('layouts.guest')

@section('content')
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <header class="w-full top-0 z-50 transition-all duration-300 backdrop-blur-md border-b border-transparent bg-white/50 dark:bg-gray-950/50 sticky">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/" class="font-bold text-xl tracking-tight">سیستم CRM</a>
            </div>
            <nav class="flex items-center gap-4">
                <a href="{{ route('properties.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">بازگشت به لیست</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Gallery -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-800" id="gallery-container">
                    @php
                        $images = $property->images;
                        $mainImage = $images->first() ? asset('storage/' . $images->first()->path) : ($property->cover_image ? asset('storage/' . $property->cover_image) : null);
                    @endphp

                    @if($mainImage)
                        <div class="relative h-96 group cursor-pointer" onclick="openLightbox()">
                            <img id="main-image" src="{{ $mainImage }}" alt="{{ $property->title }}" class="w-full h-full object-cover transition duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition duration-300 flex items-center justify-center">
                                <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition duration-300 drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                            @if($images->count() > 0)
                                <div class="absolute bottom-4 right-4 bg-black/60 text-white px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                                    {{ $images->count() }} تصویر
                                </div>
                            @endif
                        </div>

                        @if($images->count() > 1)
                            <div class="relative group/thumbs px-8 py-4">
                                <button onclick="scrollThumbnails('right')" class="absolute right-1 top-1/2 -translate-y-1/2 bg-white/80 dark:bg-gray-800/80 p-2 rounded-full shadow-md hover:bg-white dark:hover:bg-gray-800 z-10 hidden md:flex items-center justify-center text-gray-700 dark:text-gray-200 transition opacity-0 group-hover/thumbs:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>

                                <div id="thumbnails-scroll" class="flex gap-3 overflow-x-auto scrollbar-hide snap-x snap-mandatory scroll-smooth">
                                    @foreach($images as $image)
                                        <div class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden cursor-pointer border-2 border-transparent hover:border-indigo-500 transition snap-start"
                                             onclick="changeMainImage('{{ asset('storage/' . $image->path) }}')">
                                            <img src="{{ asset('storage/' . $image->path) }}" alt="" class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                </div>

                                <button onclick="scrollThumbnails('left')" class="absolute left-1 top-1/2 -translate-y-1/2 bg-white/80 dark:bg-gray-800/80 p-2 rounded-full shadow-md hover:bg-white dark:hover:bg-gray-800 z-10 hidden md:flex items-center justify-center text-gray-700 dark:text-gray-200 transition opacity-0 group-hover/thumbs:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="h-96 bg-gray-200 flex items-center justify-center text-gray-400">
                            بدون تصویر
                        </div>
                    @endif
                </div>

                <!-- Description -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-8 border border-gray-100 dark:border-gray-800">
                    <h2 class="text-2xl font-bold mb-6">توضیحات ملک</h2>
                    <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $property->description }}
                    </div>
                </div>

                <!-- Features -->
                @php
                    $features = $property->attributeValues->filter(function($attr) {
                        return $attr->attribute->section === 'features' && $attr->value == '1';
                    });
                @endphp
                @if($features->count() > 0)
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-8 border border-gray-100 dark:border-gray-800">
                        <h2 class="text-2xl font-bold mb-6">امکانات و ویژگی‌ها</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($features as $feature)
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>{{ $feature->attribute->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Location Map -->
                @if($property->latitude && $property->longitude)
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-8 border border-gray-100 dark:border-gray-800">
                        <h2 class="text-2xl font-bold mb-6">موقعیت مکانی</h2>
                        <div class="mb-4 text-gray-600 dark:text-gray-300 flex items-start gap-2">
                            <svg class="w-6 h-6 text-indigo-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="text-lg">{{ $property->address }}</p>
                        </div>
                        <div id="map" class="w-full h-80 rounded-xl z-0"></div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Key Info Card -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-800 sticky top-24">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold mb-2">{{ $property->title }}</h1>
                        <p class="text-gray-500 text-sm">کد ملک: {{ $property->code }}</p>
                    </div>

                    <div class="flex flex-col gap-4 mb-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl">
                        @if($property->listing_type === 'rent')
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 dark:text-gray-400">رهن:</span>
                                <span class="text-lg font-bold text-indigo-600">{{ number_format($property->deposit_price) }} تومان</span>
                            </div>
                            <div class="border-t border-indigo-100 dark:border-indigo-800"></div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 dark:text-gray-400">اجاره:</span>
                                <span class="text-lg font-bold text-indigo-600">{{ number_format($property->rent_price) }} تومان</span>
                            </div>
                        @else
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 dark:text-gray-400">قیمت کل:</span>
                                <span class="text-2xl font-bold text-indigo-600">{{ number_format($property->price) }} تومان</span>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-6 text-center">
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">متراژ</p>
                            <p class="font-bold">{{ $property->area }} متر</p>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">اتاق</p>
                            <p class="font-bold">{{ $property->bedrooms ?? '-' }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">نوع</p>
                            <p class="font-bold">
                                @if($property->property_type == 'apartment') آپارتمان
                                @elseif($property->property_type == 'villa') ویلایی
                                @elseif($property->property_type == 'land') زمین
                                @elseif($property->property_type == 'commercial') تجاری
                                @else {{ $property->property_type }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="font-bold text-lg mb-2">مشخصات کلی</h3>
                        @php
                            $details = $property->attributeValues->filter(function($attr) {
                                return $attr->attribute->section === 'details';
                            });
                        @endphp
                        @foreach($details as $detail)
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800 last:border-0">
                                <span class="text-gray-500">{{ $detail->attribute->name }}</span>
                                <span class="font-medium">{{ $detail->value }}</span>
                            </div>
                        @endforeach
                         <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800 last:border-0">
                            <span class="text-gray-500">نوع سند</span>
                            <span class="font-medium">
                                {{ \Modules\Properties\Entities\Property::DOCUMENT_TYPES[$property->document_type] ?? $property->document_type }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xl">
                                {{ substr($property->creator->name ?? 'A', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">مشاور شما</p>
                                <p class="font-bold">{{ $property->creator->name ?? 'مشاور املاک' }}</p>
                            </div>
                        </div>
                        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-indigo-200 dark:shadow-none flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            تماس با مشاور
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="fixed inset-0 z-[100] bg-black/90 hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-[101]">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <button onclick="prevImage()" class="absolute left-4 text-white hover:text-gray-300 z-[101] p-2 bg-black/50 rounded-full">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button onclick="nextImage()" class="absolute right-4 text-white hover:text-gray-300 z-[101] p-2 bg-black/50 rounded-full">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        <img id="lightbox-image" src="" class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg shadow-2xl" alt="Full size">
    </div>

    <script>
        // Gallery Logic
        function changeMainImage(src) {
            const mainImage = document.getElementById('main-image');
            mainImage.style.opacity = '0';
            setTimeout(() => {
                mainImage.src = src;
                mainImage.style.opacity = '1';
            }, 200);
        }

        function scrollThumbnails(direction) {
            const container = document.getElementById('thumbnails-scroll');
            const scrollAmount = 200;
            if (direction === 'left') {
                container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        }

        // Lightbox Logic
        const images = [
            @if($property->images->count() > 0)
                @foreach($property->images as $image)
                    "{{ asset('storage/' . $image->path) }}",
                @endforeach
            @elseif($property->cover_image)
                "{{ asset('storage/' . $property->cover_image) }}"
            @endif
        ];

        let currentImageIndex = 0;
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');

        function openLightbox() {
            if (images.length === 0) return;

            // Find current image index based on main image src
            const currentSrc = document.getElementById('main-image').src;
            currentImageIndex = images.findIndex(img => img === currentSrc);
            if (currentImageIndex === -1) currentImageIndex = 0;

            lightboxImage.src = images[currentImageIndex];
            lightbox.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => {
                lightbox.classList.remove('opacity-0');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.add('opacity-0');
            setTimeout(() => {
                lightbox.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            lightboxImage.src = images[currentImageIndex];
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            lightboxImage.src = images[currentImageIndex];
        }

        // Close lightbox on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
        });

        // Map Logic
        document.addEventListener('DOMContentLoaded', function() {
            @if($property->latitude && $property->longitude)
                if (typeof L !== 'undefined') {
                    const map = L.map('map').setView([{{ $property->latitude }}, {{ $property->longitude }}], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    L.marker([{{ $property->latitude }}, {{ $property->longitude }}]).addTo(map)
                        .bindPopup('{{ $property->title }}')
                        .openPopup();
                } else {
                    console.error('Leaflet is not loaded');
                }
            @endif
        });
    </script>
@endsection
