@extends('layouts.user')
@php($title = 'مدیریت موجودی انبار') {{-- 💡 عنوان تغییر کرد --}}
@section('content')
    @livewire('market::admin.warehouse-stock-controller') {{-- 💡 حذف پارامتر warehouseId --}}
@endsection
