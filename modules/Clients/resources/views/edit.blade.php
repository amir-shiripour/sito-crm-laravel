@extends('layouts.app')

@section('content')
    <h1>Edit Client</h1>
    <form action="{{ route('clients.update', $client) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3"><label>Name</label><input name="name" value="{{ $client->name }}" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input name="email" value="{{ $client->email }}" class="form-control"></div>
        <div class="mb-3"><label>Phone</label><input name="phone" value="{{ $client->phone }}" class="form-control"></div>
        <div class="mb-3"><label>Notes</label><textarea name="notes" class="form-control">{{ $client->notes }}</textarea></div>
        <button class="btn btn-primary">Update</button>
    </form>
@endsection
