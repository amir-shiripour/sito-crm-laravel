@extends('layouts.user')

@section('title', 'مدیریت خدمات و رسته‌های فنی')

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::categories-manager')
</div>
@endsection
