@extends('layouts.user')
@php($title = 'ایجاد محصول جدید')
@section('content')
    @livewire('market::vendor.product-form', key('product-form-create'))
@endsection
