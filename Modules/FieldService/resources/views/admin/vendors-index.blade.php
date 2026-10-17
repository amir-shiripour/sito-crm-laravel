@extends('layouts.user')

@section('title', 'فروشگاه‌های همکار و گارانتی لوازم خانگی')

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::vendors-manager')
</div>
@endsection
