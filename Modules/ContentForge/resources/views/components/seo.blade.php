@props(['post'])

@php
    $globalSettings = \Modules\Settings\Entities\Setting::pluck('value', 'key')->toArray();
    $appName = $globalSettings['app_name'] ?? config('app.name', 'CRM هوشمند');
@endphp

@section('title', $post->seo_title ?? $post->title)

@push('meta')
    <meta name="description" content="{{ $post->seo_description }}">
    <meta name="keywords" content="{{ $post->seo_keywords }}">

    @if($post->visibility->value !== 'public')
        <meta name="robots" content="noindex, nofollow">
    @else
        <meta name="robots" content="index, follow">
    @endif

    <link rel="canonical" href="{{ $post->canonical_url ?? url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="{{ $post->type->value === 'post' ? 'article' : 'website' }}">
    <meta property="og:title" content="{{ $post->seo_title ?? $post->title }}">
    <meta property="og:description" content="{{ $post->seo_description }}">
    <meta property="og:image" content="{{ $post->og_image ? asset('storage/' . $post->og_image) : ($post->cover_image ? asset('storage/' . $post->cover_image) : '') }}">
    <meta property="og:url" content="{{ url()->current() }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $post->seo_title ?? $post->title }}">
    <meta name="twitter:description" content="{{ $post->seo_description }}">
    <meta name="twitter:image" content="{{ $post->og_image ? asset('storage/' . $post->og_image) : ($post->cover_image ? asset('storage/' . $post->cover_image) : '') }}">

    {{-- Schema Markup --}}
    @if($post->schema_markup)
        <script type="application/ld+json">
            {!! json_encode($post->schema_markup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif
@endpush
