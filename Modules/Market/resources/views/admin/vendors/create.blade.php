@extends('layouts.user')
@php($title = 'ثبت فروشگاه جدید')
@section('content')
    @livewire('market::admin.vendor-form', key('vendor-form-create'))
@endsection
