@extends('layouts.user')
@php($title = 'ویرایش محصول کاتالوگ')
@section('content')
    @livewire('market::admin.master-product-form', ['product' => $product])
@endsection
