@extends('layouts.user')

@section('title', 'مدیریت متخصصین و ناوگان فنی')

@section('content')
<div class="space-y-4 font-sans">
    @livewire('fieldservice::technicians-manager')
</div>
@endsection
