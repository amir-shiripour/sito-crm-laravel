@extends('layouts.user')
@php($title = 'ایجاد '.config('clients.labels.singular'))

@section('content')
    @livewire('clients.form', ['asQuickWidget' => false], key('clients-form-create'))
@endsection
