@extends('layouts.user')
@php($title = 'ویرایش محصول')
@section('content')
    @livewire('market::vendor.product-form', ['product' => $product], key('product-form-edit-'.$product->id))
@endsection
