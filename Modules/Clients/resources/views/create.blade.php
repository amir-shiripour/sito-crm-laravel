@extends('layouts.app')

@section('content')
    <h1>Create Client</h1>
    <form action="{{ route('clients.store') }}" method="POST">
        @csrf
        <div class="mb-3"><label>Name</label><input name="name" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input name="email" class="form-control"></div>
        <div class="mb-3"><label>Phone</label><input name="phone" class="form-control"></div>
        <div class="mb-3"><label>Notes</label><textarea name="notes" class="form-control"></textarea></div>
        <button class="btn btn-primary">Save</button>
    </form>
@endsection
