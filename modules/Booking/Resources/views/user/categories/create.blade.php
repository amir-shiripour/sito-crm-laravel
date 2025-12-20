@extends('layouts.user')

@section('content')
    <div class="max-w-2xl space-y-6">
        <h1 class="text-xl font-bold">ایجاد دسته‌بندی</h1>

        <form method="POST" action="{{ route('user.booking.categories.store') }}" class="bg-white rounded border p-6">
            @csrf
            @include('booking::user.categories._form')
        </form>
    </div>
@endsection
