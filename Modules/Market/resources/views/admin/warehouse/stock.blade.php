@extends('layouts.user')
@php($title = 'موجودی انبار')
@section('content')
    @livewire('market::admin.warehouse-stock-controller', ['warehouseId' => request()->route('warehouseId')])
@endsection

