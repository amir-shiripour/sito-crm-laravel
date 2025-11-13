@extends('layouts.user')
@php($title = 'ویرایش '.config('clients.labels.singular'))

@section('content')
    @livewire('clients.form', ['asQuickWidget' => false, 'client' => $client], key('clients-form-edit-'.$client->id))
@endsection
