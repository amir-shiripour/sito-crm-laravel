@extends('layouts.user')

@section('content')
    <div class="container mx-auto px-4 py-8">
        @livewire('properties.settings.csv-importer')
    </div>
@endsection
