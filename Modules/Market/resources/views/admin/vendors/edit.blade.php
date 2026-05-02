@extends('layouts.user')
@php($title = 'ویرایش فروشگاه')
@section('content')
    @livewire('market::admin.vendor-form', ['vendor' => $vendor], key('vendor-form-edit-'.$vendor->id))
@endsection
